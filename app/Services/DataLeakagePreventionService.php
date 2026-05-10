<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ChatMessage;
use App\Models\ClassSession;
use App\Models\DataLeakageIncident;
use App\Models\SpeechLog;

class DataLeakagePreventionService
{
    // Regex patterns for sensitive data detection
    private array $patterns = [
        'phone_number' => [
            '/(\+?966|0096|05)\d{8,9}/',                    // Saudi phone
            '/(\+?20|002)\d{9,11}/',                         // Egyptian phone
            '/(\+?971|00971|05)\d{8,9}/',                    // UAE phone
            '/\b(\+?1[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/', // US/International
            '/\b0[0-9]{9,10}\b/',                             // Generic Arabic-region mobile
        ],
        'email' => [
            '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
        ],
        'social_media' => [
            '/@[a-zA-Z0-9_.]{3,30}/',           // @username handles
            '/واتساب|whatsapp|تيليجرام|telegram|انستجرام|instagram|تويتر|twitter/iu',
            '/t\.me\/[a-zA-Z0-9_]+/',
        ],
    ];

    /**
     * Scan a chat message for sensitive data.
     */
    public function scanChatMessage(ChatMessage $message): bool
    {
        $detected = $this->detectInText($message->message);

        if (empty($detected)) {
            return false;
        }

        foreach ($detected as $type => $matches) {
            foreach ($matches as $match) {
                $incident = DataLeakageIncident::create([
                    'class_session_id' => $message->class_session_id,
                    'user_id'          => $message->sender_id,
                    'channel'          => 'chat',
                    'detected_content' => $match,
                    'leakage_type'     => $type,
                    'chat_message_id'  => $message->id,
                ]);

                $this->sendLeakageAlert($incident, $message->classSession);

                $incident->update(['alert_sent' => true]);
            }
        }

        $message->update([
            'is_flagged'  => true,
            'flag_reason' => 'Sensitive data detected: ' . implode(', ', array_keys($detected)),
        ]);

        return true;
    }

    /**
     * Scan speech transcript for sensitive data.
     */
    public function scanSpeechLog(SpeechLog $speechLog): bool
    {
        $detected = $this->detectInText($speechLog->transcript);

        if (empty($detected)) {
            return false;
        }

        foreach ($detected as $type => $matches) {
            foreach ($matches as $match) {
                $incident = DataLeakageIncident::create([
                    'class_session_id' => $speechLog->class_session_id,
                    'user_id'          => $speechLog->speaker_id,
                    'channel'          => 'voice',
                    'detected_content' => $match,
                    'leakage_type'     => $type,
                    'speech_log_id'    => $speechLog->id,
                ]);

                $this->sendLeakageAlert($incident, $speechLog->classSession);

                $incident->update(['alert_sent' => true]);
            }
        }

        return true;
    }

    /**
     * Detect sensitive patterns in text.
     */
    public function detectInText(string $text): array
    {
        $results = [];

        foreach ($this->patterns as $type => $patternList) {
            foreach ($patternList as $pattern) {
                if (preg_match_all($pattern, $text, $matches)) {
                    $results[$type] = array_merge($results[$type] ?? [], $matches[0]);
                }
            }
        }

        return $results;
    }

    private function sendLeakageAlert(DataLeakageIncident $incident, ClassSession $session): void
    {
        $channel = $incident->channel === 'chat' ? 'Chat' : 'Voice';
        $type    = str_replace('_', ' ', $incident->leakage_type);

        Alert::create([
            'triggered_by'     => $incident->user_id,
            'target_user_id'   => null,
            'class_session_id' => $session->id,
            'type'             => "data_leakage_{$incident->channel}",
            'severity'         => 'critical',
            'title'            => "Data Leakage Detected ({$channel})",
            'message'          => "User shared {$type} during session: {$session->title}. Content: {$incident->detected_content}",
            'context'          => [
                'user_id'          => $incident->user_id,
                'channel'          => $incident->channel,
                'leakage_type'     => $incident->leakage_type,
                'detected_content' => $incident->detected_content,
            ],
        ]);
    }

    /**
     * Redact sensitive data from text for display.
     */
    public function redactText(string $text): string
    {
        foreach ($this->patterns as $type => $patternList) {
            foreach ($patternList as $pattern) {
                $text = preg_replace($pattern, '[REDACTED]', $text);
            }
        }
        return $text;
    }
}
