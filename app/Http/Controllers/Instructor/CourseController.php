<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\CourseMaterial;
use App\Models\User;
use App\Services\StudentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct(private StudentNotificationService $studentNotifications) {}

    public function index()
    {
        $courses = Course::where('instructor_id', Auth::id())->latest()->get();

        return view('instructor.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('instructor.courses.create');
    }

    public function show(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load([
            'students',
            'sessions' => fn ($query) => $query->latest('scheduled_at'),
            'assessments',
            'materials' => fn ($query) => $query->latest(),
            'assignments.submissions.student',
        ]);

        return view('instructor.courses.show', compact('course'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $subscription = $user->subscription;

        if (
            !$subscription ||
            !$subscription->isActive()
        ) {
            return redirect()
                ->route('instructor.pricing')
                ->with('error', 'You need an active subscription to create courses.');
        }

        $plan = $subscription->plan;

        $currentCoursesCount = Course::where('instructor_id', $user->id)->count();

        if (
            $plan->max_courses !== null &&
            $currentCoursesCount >= $plan->max_courses
        ) {
            return redirect()
                ->route('instructor.pricing')
                ->with('error', 'You have reached your course limit. Please upgrade your plan.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:100',
            'duration_hours' => 'nullable|integer',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $data['instructor_id'] = $user->id;

        Course::create($data);

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course created successfully');
    }

    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        return view('instructor.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'level'          => 'nullable|string',
            'language'       => 'nullable|string',
            'duration_hours' => 'nullable|integer',
            'status'         => 'nullable|in:active,inactive,archived',
        ]);

        $wasArchived = $course->status === 'archived';

        $course->update($validated);

        if (!$wasArchived && $course->status === 'archived') {
            $this->studentNotifications->notifyCourseCompleted($course, Auth::user());
        }

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course updated successfully');
    }

    public function storeMaterial(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:file,link',
            'file' => 'required_if:type,file|nullable|file|max:20480',
            'url' => 'required_if:type,link|nullable|url|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $materialData = [
            'course_id' => $course->id,
            'uploaded_by' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'url' => $data['type'] === 'link' ? $data['url'] : null,
            'is_published' => $request->boolean('is_published', true),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $materialData['file_path'] = $file->store("course-materials/{$course->id}", 'public');
            $materialData['file_name'] = $file->getClientOriginalName();
            $materialData['file_mime'] = $file->getClientMimeType();
            $materialData['file_size'] = $file->getSize();
        }

        CourseMaterial::create($materialData);

        return back()->with('success', 'Course material added.');
    }

    public function destroyMaterial(Course $course, CourseMaterial $material)
    {
        $this->authorizeCourse($course);
        abort_unless($material->course_id === $course->id, 404);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return back()->with('success', 'Course material removed.');
    }

    public function storeAssignment(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:5000',
            'due_at' => 'nullable|date',
            'points' => 'required|integer|min:1|max:1000',
            'is_published' => 'nullable|boolean',
        ]);

        $data['course_id'] = $course->id;
        $data['created_by'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published', true);

        CourseAssignment::create($data);

        return back()->with('success', 'Assignment created.');
    }

    public function destroyAssignment(Course $course, CourseAssignment $assignment)
    {
        $this->authorizeCourse($course);
        abort_unless($assignment->course_id === $course->id, 404);

        foreach ($assignment->submissions as $submission) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
        }

        $assignment->delete();

        return back()->with('success', 'Assignment removed.');
    }

    public function gradeSubmission(Request $request, Course $course, CourseAssignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeCourse($course);
        abort_unless($assignment->course_id === $course->id && $submission->course_assignment_id === $assignment->id, 404);

        $data = $request->validate([
            'grade' => "nullable|integer|min:0|max:{$assignment->points}",
            'feedback' => 'nullable|string|max:5000',
        ]);

        $submission->update($data);

        return back()->with('success', 'Submission graded.');
    }

    public function completeStudent(Course $course, User $student)
    {
        $this->authorizeCourse($course);
        abort_unless($course->students()->where('users.id', $student->id)->exists(), 404);

        $course->students()->updateExistingPivot($student->id, [
            'status' => 'completed',
        ]);

        return back()->with('success', "{$student->name} marked as completed.");
    }

    public function destroy(Course $course)
    {
        $this->authorizeCourse($course);

        $course->delete();

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === Auth::id(), 403);
    }
}
