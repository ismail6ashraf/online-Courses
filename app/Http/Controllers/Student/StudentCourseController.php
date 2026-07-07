<?php

namespace App\Http\Controllers\Student;

use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $enrollment = $student->enrolledCourses()->where('courses.id', $course->id)->first();

        abort_unless($enrollment, 403);

        $course->load(['sessions' => function ($query) {
            $query->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_at');
        }, 'instructor', 'materials' => function ($query) {
            $query->where('is_published', true)->latest();
        }, 'assignments' => function ($query) use ($student) {
            $query->where('is_published', true)
                ->with(['submissions' => fn ($submissionQuery) => $submissionQuery->where('student_id', $student->id)])
                ->latest();
        }]);

        $enrollmentStatus = $enrollment->pivot->status;

        return view('student.courses.show', compact('course', 'enrollmentStatus'));
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

        $materials = $course->materials()
            ->where('is_published', true)
            ->latest()
            ->get();

        $assignments = $course->assignments()
            ->where('is_published', true)
            ->with(['submissions' => fn ($query) => $query->where('student_id', auth()->id())])
            ->latest()
            ->get();

        return view('student.courses.details', compact(
            'course',
            'sessions',
            'assessments',
            'isEnrolled',
            'materials',
            'assignments'
        ));
    }

    public function submitAssignment(Request $request, Course $course, CourseAssignment $assignment)
    {
        $student = Auth::user();

        abort_unless($assignment->course_id === $course->id && $assignment->is_published, 404);
        abort_unless(
            $student->enrolledCourses()->where('courses.id', $course->id)->exists(),
            403
        );

        $data = $request->validate([
            'answer' => 'nullable|string|max:10000',
            'file' => 'nullable|file|max:20480',
        ]);

        abort_if(blank($data['answer'] ?? null) && !$request->hasFile('file'), 422, 'Submit an answer or attach a file.');

        $submission = AssignmentSubmission::firstOrNew([
            'course_assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        $submission->answer = $data['answer'] ?? null;
        $submission->submitted_at = now();

        if ($request->hasFile('file')) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }

            $file = $request->file('file');
            $submission->file_path = $file->store("assignment-submissions/{$assignment->id}", 'public');
            $submission->file_name = $file->getClientOriginalName();
            $submission->file_mime = $file->getClientMimeType();
            $submission->file_size = $file->getSize();
        }

        $submission->save();

        return back()->with('success', 'Assignment submitted.');
    }

    public function certificate(Course $course)
    {
        $student = Auth::user();
        $enrollment = $student->enrolledCourses()->where('courses.id', $course->id)->first();

        abort_unless($enrollment && $enrollment->pivot->status === 'completed', 403);

        $course->load('instructor');

        return view('student.courses.certificate', compact('course', 'student'));
    }
}
