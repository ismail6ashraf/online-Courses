<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ClassSession;
use App\Models\SpeechLog;
use App\Services\DataLeakagePreventionService;
use App\Services\DeadAirDetectorService;
use App\Services\SpeechAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionMonitorController extends Controller
{
    public function __construct(
        private DeadAirDetectorService $deadAirDetector,
        private DataLeakagePreventionService $dlpService,
        private SpeechAnalyzerService $speechAnalyzer,
    ) {}

    /**
     * Report audio activity heartbeat (called every 5s from frontend).
     */
    public function heartbeat(Request $request, ClassSession $session): JsonResponse
    {
        abort_unless($session->isLive(), 422, 'Session is not live.');

        $this->deadAirDetector->reportActivity($session, $request->integer('current_second', 0));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Report silence / dead air.
     */
    public function reportSilence(Request $request, ClassSession $session): JsonResponse
    {
        abort_unless($session->isLive(), 422);

        $log = $this->deadAirDetector->reportSilence($session, $request->integer('start_second', 0));
        $this->deadAirDetector->checkAndAlert($session, $request->integer('current_second', 0));

        return response()->json(['status' => 'logged', 'dead_air_log_id' => $log->id]);
    }

    /**
     * Submit a speech transcript chunk for analysis.
     */
    public function submitTranscript(Request $request, ClassSession $session): JsonResponse
    {
        $data = $request->validate([
            'transcript'         => 'required|string|min:3',
            'start_time_seconds' => 'nullable|integer',
            'end_time_seconds'   => 'nullable|integer',
            'confidence_score'   => 'nullable|numeric',
            'audio_file'         => 'nullable|string',
        ]);

        $speechLog = SpeechLog::create([
            'class_session_id'   => $session->id,
            'speaker_id'         => Auth::id(),
            'transcript'         => $data['transcript'],
            'start_time_seconds' => $data['start_time_seconds'] ?? null,
            'end_time_seconds'   => $data['end_time_seconds'] ?? null,
            'confidence_score'   => $data['confidence_score'] ?? null,
            'audio_file'         => $data['audio_file'] ?? null,
        ]);

        // Analyze for speech patterns (AI-based)
        $this->speechAnalyzer->processSpeechLog($speechLog);

        // Scan for data leakage
        $this->dlpService->scanSpeechLog($speechLog);

        return response()->json([
            'status'   => 'processed',
            'log_id'   => $speechLog->id,
            'analyzed' => true,
        ]);
    }

    /**
     * Submit a chat message for real-time DLP scanning.
     */
    public function submitChatMessage(Request $request, ClassSession $session): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = ChatMessage::create([
            'class_session_id' => $session->id,
            'sender_id'        => Auth::id(),
            'message'          => $data['message'],
        ]);

        $flagged = $this->dlpService->scanChatMessage($message);

        return response()->json([
            'status'    => 'sent',
            'flagged'   => $flagged,
            'message'   => $flagged ? 'Message flagged for policy violation.' : 'Message sent.',
            'redacted'  => $flagged ? $this->dlpService->redactText($data['message']) : null,
        ], $flagged ? 422 : 200);
    }

    /**
     * Get live session stats for admin/instructor.
     */
    public function liveStats(ClassSession $session): JsonResponse
    {
        return response()->json([
            'dead_air'        => $this->deadAirDetector->getSessionStats($session),
            'attendees'       => $session->attendances()->where('status', 'present')->count(),
            'chat_messages'   => $session->chatMessages()->count(),
            'flagged_messages'=> $session->chatMessages()->where('is_flagged', true)->count(),
            'behavior_alerts' => $session->behaviorIncidents()->count(),
        ]);
    }

    /**
     * Mark attendance when a user joins the session.
     */
    public function joinSession(Request $request, ClassSession $session): JsonResponse
    {
        $attendance = $session->attendances()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'status'     => 'present',
                'joined_at'  => now(),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json(['status' => 'joined', 'attendance_id' => $attendance->id]);
    }

    /**
     * Mark attendance when a user leaves the session.
     */
    public function leaveSession(ClassSession $session): JsonResponse
    {
        $attendance = $session->attendances()->where('user_id', Auth::id())->first();

        if ($attendance) {
            $duration = $attendance->joined_at
                ? (int) $attendance->joined_at->diffInMinutes(now())
                : null;

            $attendance->update([
                'left_at'          => now(),
                'duration_minutes' => $duration,
                'status'           => $duration < 30 ? 'left_early' : 'present',
            ]);
        }

        return response()->json(['status' => 'left']);
    }
}
