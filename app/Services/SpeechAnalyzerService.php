<?php

namespace App\Services;

use App\Models\BehaviorIncident;
use App\Models\ClassSession;
use App\Models\SpeechLog;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SpeechAnalyzerService
{
    private Client $client;

    // Arabic and English negative/positive keyword lists (extendable)
    private array $negativeKeywords = [
        // Arabic
        'غبي', 'فاشل', 'أحمق', 'كسول', 'لا تفهم', 'ما تعرف', 'ضعيف', 'مقصر',
        // English
        'stupid', 'idiot', 'failure', 'dumb', 'lazy', 'useless', 'incompetent',
    ];

    private array $positiveKeywords = [
        // Arabic
        'أحسنت', 'ممتاز', 'رائع', 'متميز', 'شاطر', 'نجحت', 'بارك الله فيك',
        'استمر', 'واصل', 'تحسنت', 'جيد جداً', 'عظيم', 'مبدع',
        // English
        'excellent', 'great', 'well done', 'amazing', 'fantastic', 'brilliant',
        'keep it up', 'good job', 'proud of you', 'wonderful',
    ];

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Convert audio file to text using AssemblyAI or Google Speech API.
     */
    public function transcribeAudio(string $audioFilePath, string $language = 'ar'): array
    {
        $apiKey = config('services.assemblyai.key');

        if (empty($apiKey)) {
            return $this->mockTranscription($audioFilePath);
        }

        try {
            // Upload audio to AssemblyAI
            $uploadResponse = $this->client->post('https://api.assemblyai.com/v2/upload', [
                'headers' => ['authorization' => $apiKey],
                'body'    => fopen($audioFilePath, 'r'),
            ]);

            $uploadUrl = json_decode($uploadResponse->getBody(), true)['upload_url'];

            // Request transcription
            $transcriptResponse = $this->client->post('https://api.assemblyai.com/v2/transcript', [
                'headers' => [
                    'authorization' => $apiKey,
                    'content-type'  => 'application/json',
                ],
                'json' => [
                    'audio_url'              => $uploadUrl,
                    'language_code'          => $language,
                    'word_boost'             => array_merge($this->negativeKeywords, $this->positiveKeywords),
                    'speaker_labels'         => true,
                    'sentiment_analysis'     => true,
                ],
            ]);

            $jobId = json_decode($transcriptResponse->getBody(), true)['id'];

            return $this->pollTranscriptionResult($jobId, $apiKey);
        } catch (\Exception $e) {
            Log::error('SpeechAnalyzer transcription error: ' . $e->getMessage());
            return ['text' => '', 'words' => [], 'confidence' => 0];
        }
    }

    private function pollTranscriptionResult(string $jobId, string $apiKey): array
    {
        $maxAttempts = 30;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            sleep(3);
            $response = $this->client->get("https://api.assemblyai.com/v2/transcript/{$jobId}", [
                'headers' => ['authorization' => $apiKey],
            ]);
            $data = json_decode($response->getBody(), true);

            if ($data['status'] === 'completed') {
                return [
                    'text'       => $data['text'] ?? '',
                    'words'      => $data['words'] ?? [],
                    'confidence' => $data['confidence'] ?? 0,
                    'sentiment'  => $data['sentiment_analysis_results'] ?? [],
                ];
            }

            if ($data['status'] === 'error') {
                break;
            }

            $attempt++;
        }

        return ['text' => '', 'words' => [], 'confidence' => 0];
    }

    /**
     * Analyze transcript text for negative/positive speech patterns.
     */
    public function analyzeText(string $text): array
    {
        $result = [
            'negative_phrases' => [],
            'positive_phrases' => [],
            'sentiment_score'  => 0.0,
        ];

        $lowerText = mb_strtolower($text);

        foreach ($this->negativeKeywords as $keyword) {
            if (mb_strpos($lowerText, mb_strtolower($keyword)) !== false) {
                $result['negative_phrases'][] = $keyword;
            }
        }

        foreach ($this->positiveKeywords as $keyword) {
            if (mb_strpos($lowerText, mb_strtolower($keyword)) !== false) {
                $result['positive_phrases'][] = $keyword;
            }
        }

        $negCount = count($result['negative_phrases']);
        $posCount = count($result['positive_phrases']);
        $total = $negCount + $posCount;

        if ($total > 0) {
            $result['sentiment_score'] = ($posCount - $negCount) / $total;
        }

        return $result;
    }

    /**
     * Process a speech log and create behavior incidents.
     */
    public function processSpeechLog(SpeechLog $speechLog): void
    {
        $analysis = $this->analyzeText($speechLog->transcript);

        foreach ($analysis['negative_phrases'] as $phrase) {
            BehaviorIncident::create([
                'class_session_id'  => $speechLog->class_session_id,
                'user_id'           => $speechLog->speaker_id,
                'speech_log_id'     => $speechLog->id,
                'type'              => 'negative_speech',
                'detected_phrase'   => $phrase,
                'full_context'      => $speechLog->transcript,
                'sentiment_score'   => $analysis['sentiment_score'],
                'timestamp_seconds' => $speechLog->start_time_seconds,
            ]);
        }

        foreach ($analysis['positive_phrases'] as $phrase) {
            BehaviorIncident::create([
                'class_session_id'  => $speechLog->class_session_id,
                'user_id'           => $speechLog->speaker_id,
                'speech_log_id'     => $speechLog->id,
                'type'              => 'positive_speech',
                'detected_phrase'   => $phrase,
                'full_context'      => $speechLog->transcript,
                'sentiment_score'   => $analysis['sentiment_score'],
                'timestamp_seconds' => $speechLog->start_time_seconds,
            ]);
        }

        $speechLog->update(['analyzed' => true]);
    }

    /**
     * Use OpenAI GPT for advanced NLP analysis (>95% accuracy target).
     */
    public function analyzeWithAI(string $text, string $language = 'ar'): array
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            return $this->analyzeText($text);
        }

        try {
            $prompt = $language === 'ar'
                ? "حلل النص التالي من محاضرة تعليمية وحدد: 1) أي عبارات سلبية أو مسيئة 2) أي عبارات تحفيزية أو إيجابية 3) درجة المشاعر من -1 (سلبي جداً) إلى 1 (إيجابي جداً). أجب بتنسيق JSON.\n\nالنص: {$text}"
                : "Analyze the following text from an educational lecture and identify: 1) Any negative or offensive phrases 2) Any motivational or positive phrases 3) Sentiment score from -1 (very negative) to 1 (very positive). Reply in JSON format.\n\nText: {$text}";

            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an AI that analyzes educational speech for quality monitoring. Always respond with valid JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $content = json_decode($data['choices'][0]['message']['content'], true);

            return [
                'negative_phrases' => $content['negative_phrases'] ?? [],
                'positive_phrases' => $content['positive_phrases'] ?? [],
                'sentiment_score'  => $content['sentiment_score'] ?? 0.0,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI analysis error: ' . $e->getMessage());
            return $this->analyzeText($text);
        }
    }

    private function mockTranscription(string $audioFilePath): array
    {
        return [
            'text'       => 'Mock transcript for: ' . basename($audioFilePath),
            'words'      => [],
            'confidence' => 0.95,
        ];
    }
}
