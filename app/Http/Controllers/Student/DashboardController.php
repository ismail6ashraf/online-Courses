<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        $enrolledCourseIds = $student->enrolledCourses()->pluck('courses.id');

        $stats = [
            'enrolled_courses'   => $enrolledCourseIds->count(),
            'upcoming_sessions'  => ClassSession::whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>', now())
                ->count(),
            'attendance_rate'    => $this->calculateAttendanceRate($student->id),
        ];

        $upcomingSessions = ClassSession::whereIn('course_id', $enrolledCourseIds)
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->with(['course', 'instructor'])
            ->limit(5)
            ->get();

        $recentAttendances = AttendanceLog::where('user_id', $student->id)
            ->with(['session.course'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $availableCourses = Course::where('status', 'active')->latest()->get();

        return view('student.dashboard', compact('stats', 'upcomingSessions', 'recentAttendances','availableCourses'));
    }

    private function calculateAttendanceRate(int $studentId): float
    {
        $total   = AttendanceLog::where('user_id', $studentId)->count();
        $present = AttendanceLog::where('user_id', $studentId)->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0.0;
    }
    
}
