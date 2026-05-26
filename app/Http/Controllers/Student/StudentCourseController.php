<?php

namespace App\Http\Controllers\Student;

use App\Models\Course;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentCourseController extends Controller
{
    public function enroll($courseId)
    {
        $student = Auth::user();

        if (!$student->enrolledCourses()
            ->where('course_id', $courseId)
            ->exists()) {

            $student->enrolledCourses()->attach($courseId);
        }

        return back()->with('success', 'Enrolled successfully');
    }
    public function show(Course $course)
    {
        $student = auth()->user();

        abort_unless(
            $student->enrolledCourses()->where('courses.id', $course->id)->exists(),
            403
        );

        $course->load(['sessions' => function ($query) {
            $query->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_at');
        }, 'instructor']);

        return view('student.courses.show', compact('course'));
    }

    public function details(Course $course)
    {
        $isEnrolled = auth()->user()
            ->enrolledCourses()
            ->where('courses.id', $course->id)
            ->exists();

        $sessions = $course->sessions()
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get();

        $assessments = $course->assessments()
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('student.courses.details', compact('course', 'sessions', 'assessments', 'isEnrolled'));
    }
}
