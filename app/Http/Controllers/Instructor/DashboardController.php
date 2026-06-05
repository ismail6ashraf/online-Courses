<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\ClassSession;
use App\Models\InstructorTask;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $instructor = Auth::user();

        $subscription = $instructor->subscription;
        $plan = $subscription?->plan;
        $coursesCount = $instructor->coursesAsInstructor()->count();

        $stats = [
            'total_courses' => $coursesCount,

            'upcoming_sessions' => ClassSession::where('instructor_id', $instructor->id)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())
                ->count(),

            'pending_tasks' => InstructorTask::where('instructor_id', $instructor->id)
                ->where('status', 'pending')
                ->count(),

            'unread_alerts' => Alert::where('target_user_id', $instructor->id)
                ->where('is_read', false)
                ->count(),
        ];

        $upcomingSessions = ClassSession::where('instructor_id', $instructor->id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->with('course')
            ->limit(5)
            ->get();

        $pendingTasks = InstructorTask::where('instructor_id', $instructor->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_at')
            ->with(['assessmentResponse.student', 'classSession'])
            ->limit(10)
            ->get();

        $recentAlerts = Alert::where('target_user_id', $instructor->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('instructor.dashboard', compact(
            'stats',
            'upcomingSessions',
            'pendingTasks',
            'recentAlerts',
            'subscription',
            'plan',
            'coursesCount'
        ));
    }
}
