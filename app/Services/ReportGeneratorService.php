<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AttendanceLog;
use App\Models\BehaviorIncident;
use App\Models\ClassSession;
use App\Models\DataLeakageIncident;
use App\Models\DeadAirLog;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportGeneratorService
{
    /**
     * Generate a daily report for all instructors.
     */
    public function generateDailyReport(User $generatedBy, Carbon $date = null): Report
    {
        $date  = $date ?? Carbon::today();
        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();

        $data = $this->collectPeriodData($start, $end);

        return Report::create([
            'title'        => 'Daily Report - ' . $date->format('Y-m-d'),
            'type'         => 'daily',
            'subject'      => 'overall',
            'generated_by' => $generatedBy->id,
            'period_start' => $start->toDateString(),
            'period_end'   => $end->toDateString(),
            'data'         => $data,
            'status'       => 'ready',
        ]);
    }

    /**
     * Generate a weekly report.
     */
    public function generateWeeklyReport(User $generatedBy, Carbon $weekStart = null): Report
    {
        $weekStart = $weekStart ?? Carbon::now()->startOfWeek();
        $weekEnd   = $weekStart->copy()->endOfWeek();

        $data = $this->collectPeriodData($weekStart, $weekEnd);

        return Report::create([
            'title'        => 'Weekly Report - ' . $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'type'         => 'weekly',
            'subject'      => 'overall',
            'generated_by' => $generatedBy->id,
            'period_start' => $weekStart->toDateString(),
            'period_end'   => $weekEnd->toDateString(),
            'data'         => $data,
            'status'       => 'ready',
        ]);
    }

    /**
     * Generate a monthly report.
     */
    public function generateMonthlyReport(User $generatedBy, Carbon $month = null): Report
    {
        $month = $month ?? Carbon::now()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $data = $this->collectPeriodData($month, $end);

        return Report::create([
            'title'        => 'Monthly Report - ' . $month->format('Y-m'),
            'type'         => 'monthly',
            'subject'      => 'overall',
            'generated_by' => $generatedBy->id,
            'period_start' => $month->toDateString(),
            'period_end'   => $end->toDateString(),
            'data'         => $data,
            'status'       => 'ready',
        ]);
    }

    /**
     * Generate a per-instructor performance report.
     */
    public function generateInstructorReport(User $instructor, User $generatedBy, Carbon $start, Carbon $end): Report
    {
        $sessions = ClassSession::where('instructor_id', $instructor->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->get();

        $sessionIds = $sessions->pluck('id');

        $data = [
            'instructor' => [
                'id'        => $instructor->id,
                'name'      => $instructor->name,
                'email'     => $instructor->email,
                'specialty' => $instructor->specialty,
            ],
            'sessions' => [
                'total'     => $sessions->count(),
                'completed' => $sessions->where('status', 'completed')->count(),
                'cancelled' => $sessions->where('status', 'cancelled')->count(),
            ],
            'behavior' => [
                'negative_incidents' => BehaviorIncident::whereIn('class_session_id', $sessionIds)
                    ->where('type', 'negative_speech')->count(),
                'positive_incidents' => BehaviorIncident::whereIn('class_session_id', $sessionIds)
                    ->where('type', 'positive_speech')->count(),
            ],
            'dead_air' => [
                'total_incidents'  => DeadAirLog::whereIn('class_session_id', $sessionIds)->count(),
                'total_seconds'    => DeadAirLog::whereIn('class_session_id', $sessionIds)->sum('duration_seconds'),
            ],
            'data_leakage' => [
                'total_incidents' => DataLeakageIncident::whereIn('class_session_id', $sessionIds)->count(),
            ],
            'attendance' => [
                'average_attendance_rate' => $this->calculateAverageAttendanceRate($sessionIds),
            ],
        ];

        return Report::create([
            'title'           => "Instructor Report: {$instructor->name} ({$start->format('Y-m-d')} to {$end->format('Y-m-d')})",
            'type'            => 'custom',
            'subject'         => 'instructor',
            'generated_by'    => $generatedBy->id,
            'subject_user_id' => $instructor->id,
            'period_start'    => $start->toDateString(),
            'period_end'      => $end->toDateString(),
            'data'            => $data,
            'status'          => 'ready',
        ]);
    }

    /**
     * Generate a session-specific report.
     */
    public function generateSessionReport(ClassSession $session, User $generatedBy): Report
    {
        $session->loadMissing(['course.students', 'instructor']);

        $attendanceLogs = AttendanceLog::where('class_session_id', $session->id)->with('user')->get();
        $behaviorIncidents = BehaviorIncident::where('class_session_id', $session->id)->with('user')->get();
        $deadAirLogs = DeadAirLog::where('class_session_id', $session->id)->get();
        $dataLeakageIncidents = DataLeakageIncident::where('class_session_id', $session->id)->with('user')->get();
        $alerts = Alert::where('class_session_id', $session->id)->get();
        $expectedStudents = $session->course?->students?->count() ?? 0;
        $presentStudents = $attendanceLogs->whereIn('status', ['present', 'left_early', 'late'])->count();
        $attendanceRate = $expectedStudents > 0
            ? round(($presentStudents / $expectedStudents) * 100, 1)
            : 0.0;
        $negativeSpeechCount = $behaviorIncidents->where('type', 'negative_speech')->count();
        $positiveSpeechCount = $behaviorIncidents->where('type', 'positive_speech')->count();
        $totalDeadAirSeconds = (int) $deadAirLogs->sum('duration_seconds');
        $criticalAlerts = $alerts->where('severity', 'critical')->count();

        $data = [
            'summary' => [
                'attendance_rate'        => $attendanceRate,
                'expected_students'      => $expectedStudents,
                'present_students'       => $presentStudents,
                'total_dead_air_seconds' => $totalDeadAirSeconds,
                'negative_speech_count'  => $negativeSpeechCount,
                'positive_speech_count'  => $positiveSpeechCount,
                'data_leakage_count'     => $dataLeakageIncidents->count(),
                'critical_alerts'        => $criticalAlerts,
                'overall_status'         => $this->resolveSessionHealth(
                    $attendanceRate,
                    $totalDeadAirSeconds,
                    $negativeSpeechCount,
                    $dataLeakageIncidents->count(),
                    $criticalAlerts
                ),
            ],
            'session' => [
                'id'         => $session->id,
                'title'      => $session->title,
                'instructor' => $session->instructor->name ?? 'N/A',
                'course'     => $session->course->title ?? 'N/A',
                'status'     => $session->status,
                'started_at' => $session->started_at?->toDateTimeString(),
                'ended_at'   => $session->ended_at?->toDateTimeString(),
                'duration'   => $session->duration_minutes,
            ],
            'attendance' => [
                'by_status' => $attendanceLogs->countBy('status')->toArray(),
                'rate'      => $attendanceRate,
                'students'  => $attendanceLogs->map(fn ($attendance) => [
                    'student'          => $attendance->user?->name,
                    'status'           => $attendance->status,
                    'joined_at'        => $attendance->joined_at?->toDateTimeString(),
                    'left_at'          => $attendance->left_at?->toDateTimeString(),
                    'duration_minutes' => $attendance->duration_minutes,
                ])->values()->toArray(),
            ],
            'behavior' => [
                'negative'  => $negativeSpeechCount,
                'positive'  => $positiveSpeechCount,
                'incidents' => $behaviorIncidents->map(fn ($incident) => [
                    'user'             => $incident->user?->name,
                    'type'             => $incident->type,
                    'detected_phrase'  => $incident->detected_phrase,
                    'sentiment_score'  => $incident->sentiment_score,
                    'timestamp_seconds'=> $incident->timestamp_seconds,
                ])->values()->toArray(),
            ],
            'dead_air' => [
                'incidents'        => $deadAirLogs->toArray(),
                'total_seconds'    => $totalDeadAirSeconds,
                'longest_incident' => (int) $deadAirLogs->max('duration_seconds'),
            ],
            'data_leakage' => [
                'total'      => $dataLeakageIncidents->count(),
                'by_channel' => $dataLeakageIncidents->countBy('channel')->toArray(),
                'by_type'    => $dataLeakageIncidents->countBy('leakage_type')->toArray(),
                'incidents'  => $dataLeakageIncidents->map(fn ($incident) => [
                    'user'         => $incident->user?->name,
                    'channel'      => $incident->channel,
                    'leakage_type' => $incident->leakage_type,
                ])->values()->toArray(),
            ],
            'alerts' => [
                'total'       => $alerts->count(),
                'critical'    => $criticalAlerts,
                'warning'     => $alerts->where('severity', 'warning')->count(),
                'by_type'     => $alerts->countBy('type')->toArray(),
            ],
            'recommendations' => $this->buildSessionRecommendations(
                $attendanceRate,
                $totalDeadAirSeconds,
                $negativeSpeechCount,
                $dataLeakageIncidents->count(),
                $criticalAlerts
            ),
        ];

        return Report::create([
            'title'            => "Session Report: {$session->title}",
            'type'             => 'session',
            'subject'          => 'session',
            'generated_by'     => $generatedBy->id,
            'class_session_id' => $session->id,
            'period_start'     => $session->scheduled_at->toDateString(),
            'period_end'       => $session->ended_at?->toDateString() ?? $session->scheduled_at->toDateString(),
            'data'             => $data,
            'status'           => 'ready',
        ]);
    }

    private function resolveSessionHealth(
        float $attendanceRate,
        int $deadAirSeconds,
        int $negativeSpeechCount,
        int $dataLeakageCount,
        int $criticalAlerts
    ): string {
        if ($criticalAlerts > 0 || $dataLeakageCount > 0 || $negativeSpeechCount >= 3 || $deadAirSeconds >= 180) {
            return 'needs_review';
        }

        if ($attendanceRate > 0 && $attendanceRate < 60) {
            return 'low_attendance';
        }

        if ($deadAirSeconds >= 60 || $negativeSpeechCount > 0) {
            return 'watch';
        }

        return 'healthy';
    }

    private function buildSessionRecommendations(
        float $attendanceRate,
        int $deadAirSeconds,
        int $negativeSpeechCount,
        int $dataLeakageCount,
        int $criticalAlerts
    ): array {
        $recommendations = [];

        if ($attendanceRate > 0 && $attendanceRate < 70) {
            $recommendations[] = 'Follow up with absent or low-attendance students before the next session.';
        }

        if ($deadAirSeconds >= 60) {
            $recommendations[] = 'Review the session recording around silence periods and confirm the meeting audio setup.';
        }

        if ($negativeSpeechCount > 0) {
            $recommendations[] = 'Review flagged speech incidents and coach the instructor on constructive classroom language.';
        }

        if ($dataLeakageCount > 0) {
            $recommendations[] = 'Review data leakage incidents and remind participants not to share private contact details.';
        }

        if ($criticalAlerts > 0) {
            $recommendations[] = 'Prioritize this report for admin review because it contains critical alerts.';
        }

        return $recommendations ?: ['No immediate follow-up needed.'];
    }

    private function collectPeriodData(Carbon $start, Carbon $end): array
    {
        $sessions = ClassSession::whereBetween('scheduled_at', [$start, $end])->get();
        $sessionIds = $sessions->pluck('id');

        return [
            'period'         => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
            'sessions'       => ['total' => $sessions->count(), 'completed' => $sessions->where('status', 'completed')->count()],
            'behavior'       => [
                'negative' => BehaviorIncident::whereIn('class_session_id', $sessionIds)->where('type', 'negative_speech')->count(),
                'positive' => BehaviorIncident::whereIn('class_session_id', $sessionIds)->where('type', 'positive_speech')->count(),
            ],
            'dead_air'       => ['total_seconds' => DeadAirLog::whereIn('class_session_id', $sessionIds)->sum('duration_seconds')],
            'data_leakage'   => ['total' => DataLeakageIncident::whereIn('class_session_id', $sessionIds)->count()],
            'alerts'         => ['total' => Alert::whereIn('class_session_id', $sessionIds)->count()],
        ];
    }

    private function calculateAverageAttendanceRate($sessionIds): float
    {
        if ($sessionIds->isEmpty()) {
            return 0.0;
        }

        $rates = [];
        foreach ($sessionIds as $sessionId) {
            $total   = AttendanceLog::where('class_session_id', $sessionId)->count();
            $present = AttendanceLog::where('class_session_id', $sessionId)->where('status', 'present')->count();
            if ($total > 0) {
                $rates[] = ($present / $total) * 100;
            }
        }

        return count($rates) > 0 ? array_sum($rates) / count($rates) : 0.0;
    }
}
