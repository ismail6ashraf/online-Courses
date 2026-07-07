<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AssignmentSubmission;
use App\Models\ClassSession;
use App\Models\CourseAssignment;
use App\Models\CourseMaterial;
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
            'total_students' => $instructor->coursesAsInstructor()
                ->withCount('students')
                ->get()
                ->sum('students_count'),

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

            'course_materials' => CourseMaterial::whereHas('course', fn ($query) => $query->where('instructor_id', $instructor->id))->count(),
            'published_assignments' => CourseAssignment::where('created_by', $instructor->id)->where('is_published', true)->count(),
            'assignment_submissions' => AssignmentSubmission::whereHas('assignment', fn ($query) => $query->where('created_by', $instructor->id))->count(),
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

        $recentSubmissions = AssignmentSubmission::with(['student', 'assignment.course'])
            ->whereHas('assignment', fn ($query) => $query->where('created_by', $instructor->id))
            ->orderByDesc('submitted_at')
            ->limit(6)
            ->get();

        $sessionMix = [
            'labels' => ['Scheduled', 'Live', 'Completed', 'Cancelled'],
            'values' => [
                ClassSession::where('instructor_id', $instructor->id)->where('status', 'scheduled')->count(),
                ClassSession::where('instructor_id', $instructor->id)->where('status', 'live')->count(),
                ClassSession::where('instructor_id', $instructor->id)->where('status', 'completed')->count(),
                ClassSession::where('instructor_id', $instructor->id)->where('status', 'cancelled')->count(),
            ],
        ];

        return view('instructor.dashboard', compact(
            'stats',
            'upcomingSessions',
            'pendingTasks',
            'recentAlerts',
            'recentSubmissions',
            'sessionMix',
            'subscription',
            'plan',
            'coursesCount'
        ));
    }
}
