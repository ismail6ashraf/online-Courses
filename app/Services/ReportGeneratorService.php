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
        $data = [
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
            'attendance' => AttendanceLog::where('class_session_id', $session->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'behavior' => [
                'negative' => BehaviorIncident::where('class_session_id', $session->id)
                    ->where('type', 'negative_speech')->count(),
                'positive' => BehaviorIncident::where('class_session_id', $session->id)
                    ->where('type', 'positive_speech')->count(),
                'incidents' => BehaviorIncident::where('class_session_id', $session->id)
                    ->with('user')->get()->toArray(),
            ],
            'dead_air' => [
                'incidents'     => DeadAirLog::where('class_session_id', $session->id)->get()->toArray(),
                'total_seconds' => $session->dead_air_seconds,
            ],
            'data_leakage' => DataLeakageIncident::where('class_session_id', $session->id)
                ->with('user')->get()->toArray(),
            'alerts' => Alert::where('class_session_id', $session->id)->count(),
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
