<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ClassSession;
use App\Models\DeadAirLog;
use Illuminate\Support\Facades\Log;

class DeadAirDetectorService
{
    // Silence threshold in seconds before flagging as dead air
    private int $silenceThreshold = 30;

    // Alert after this many seconds of silence
    private int $alertThreshold = 60;

    public function __construct()
    {
        $this->silenceThreshold = config('classroom.dead_air_threshold_seconds', 30);
        $this->alertThreshold   = config('classroom.dead_air_alert_seconds', 60);
    }

    /**
     * Report a heartbeat/audio signal from a session.
     * Called periodically by the frontend via API.
     */
    public function reportActivity(ClassSession $session, int $currentSecond): void
    {
        // Resolve any ongoing dead air logs
        $ongoing = DeadAirLog::where('class_session_id', $session->id)
            ->where('status', 'ongoing')
            ->first();

        if ($ongoing) {
            $duration = $currentSecond - $ongoing->start_second;
            $ongoing->update([
                'end_second'       => $currentSecond,
                'duration_seconds' => $duration,
                'status'           => 'resolved',
            ]);

            // Update session total dead air
            $session->increment('dead_air_seconds', $duration);

            Log::info("Dead air resolved for session #{$session->id}: {$duration}s");
        }
    }

    /**
     * Report silence from a session.
     * Called when no audio is detected for silenceThreshold seconds.
     */
    public function reportSilence(ClassSession $session, int $startSecond): DeadAirLog
    {
        $existing = DeadAirLog::where('class_session_id', $session->id)
            ->where('status', 'ongoing')
            ->first();

        if ($existing) {
            return $existing;
        }

        $log = DeadAirLog::create([
            'class_session_id' => $session->id,
            'start_second'     => $startSecond,
            'status'           => 'ongoing',
            'alert_sent'       => false,
        ]);

        Log::info("Dead air started for session #{$session->id} at second {$startSecond}");

        return $log;
    }

    /**
     * Check ongoing dead air and send alerts if threshold exceeded.
     */
    public function checkAndAlert(ClassSession $session, int $currentSecond): void
    {
        $ongoing = DeadAirLog::where('class_session_id', $session->id)
            ->where('status', 'ongoing')
            ->where('alert_sent', false)
            ->first();

        if (!$ongoing) {
            return;
        }

        $elapsed = $currentSecond - $ongoing->start_second;

        if ($elapsed >= $this->alertThreshold) {
            $this->sendDeadAirAlert($session, $ongoing, $elapsed);
            $ongoing->update(['alert_sent' => true]);
        }
    }

    private function sendDeadAirAlert(ClassSession $session, DeadAirLog $log, int $durationSeconds): void
    {
        $minutes = round($durationSeconds / 60, 1);

        Alert::create([
            'triggered_by'     => null,
            'target_user_id'   => $session->instructor_id,
            'class_session_id' => $session->id,
            'type'             => 'dead_air',
            'severity'         => $durationSeconds > 120 ? 'critical' : 'warning',
            'title'            => 'Dead Air Detected',
            'message'          => "No audio activity detected for {$minutes} minutes in session: {$session->title}",
            'context'          => [
                'session_id'       => $session->id,
                'start_second'     => $log->start_second,
                'duration_seconds' => $durationSeconds,
            ],
        ]);
    }

    /**
     * Get total dead air statistics for a session.
     */
    public function getSessionStats(ClassSession $session): array
    {
        $logs = DeadAirLog::where('class_session_id', $session->id)
            ->where('status', '!=', 'ongoing')
            ->get();

        return [
            'total_incidents'      => $logs->count(),
            'total_seconds'        => $logs->sum('duration_seconds'),
            'longest_incident_sec' => $logs->max('duration_seconds') ?? 0,
            'incidents'            => $logs->toArray(),
        ];
    }
}
