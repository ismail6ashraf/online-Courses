<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\CourseAssignment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user();
        $courseIds = $student->enrolledCourses()->pluck('courses.id');
        $month = Carbon::parse($request->query('month', now()->format('Y-m')) . '-01');
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $sessions = ClassSession::with('course')
            ->whereIn('course_id', $courseIds)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('scheduled_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn ($session) => [
                'date' => $session->scheduled_at,
                'type' => 'session',
                'title' => $session->title,
                'subtitle' => $session->course->title ?? 'Course',
                'status' => $session->status,
                'url' => route('student.courses.show', $session->course_id),
            ]);

        $assignments = CourseAssignment::with(['course', 'submissions' => fn ($query) => $query->where('student_id', $student->id)])
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('due_at')
            ->get()
            ->map(fn ($assignment) => [
                'date' => $assignment->due_at,
                'type' => 'assignment',
                'title' => $assignment->title,
                'subtitle' => $assignment->course->title ?? 'Course',
                'status' => $assignment->submissions->isNotEmpty() ? 'submitted' : 'pending',
                'url' => route('student.courses.show', $assignment->course_id),
            ]);

        $events = $sessions
            ->concat($assignments)
            ->sortBy('date')
            ->groupBy(fn ($event) => $event['date']->format('Y-m-d'));

        $calendarStart = $start->copy()->startOfWeek();
        $calendarEnd = $end->copy()->endOfWeek();
        $calendarDays = collect(CarbonPeriod::create($calendarStart, $calendarEnd));

        return view('student.calendar', compact('events', 'month', 'calendarDays'));
    }
}
