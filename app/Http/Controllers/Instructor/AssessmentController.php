<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentResponse;
use App\Models\ClassSession;
use App\Models\Course;
use App\Services\AssessmentTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function __construct(private AssessmentTaskService $taskService) {}

    public function index()
    {
        $assessments = Assessment::where('created_by', Auth::id())
            ->withCount('responses')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('instructor.assessments.index', compact('assessments'));
    }

    public function create()
    {
        $courses  = Course::where('instructor_id', Auth::id())->get();
        $sessions = ClassSession::where('instructor_id', Auth::id())
            ->where('status', '!=', 'cancelled')->orderByDesc('scheduled_at')->get();

        return view('instructor.assessments.create', compact('courses', 'sessions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'course_id'        => 'nullable|exists:courses,id',
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'type'             => 'required|in:pre_session,mid_session,post_session,general',
            'fields'           => 'required|array|min:1',
            'fields.*.label'   => 'required|string',
            'fields.*.type'    => 'required|in:text,textarea,select,radio,checkbox,rating,number',
            'fields.*.options' => 'nullable|array',
            'fields.*.required'=> 'boolean',
        ]);

        $assessment = Assessment::create([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'created_by'       => Auth::id(),
            'course_id'        => $data['course_id'] ?? null,
            'class_session_id' => $data['class_session_id'] ?? null,
            'type'             => $data['type'],
            'status'           => 'active',
        ]);

        foreach ($data['fields'] as $index => $field) {
            $assessment->fields()->create([
                'label'      => $field['label'],
                'type'       => $field['type'],
                'options'    => $field['options'] ?? null,
                'required'   => $field['required'] ?? false,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('instructor.assessments.show', $assessment)
            ->with('success', 'Assessment created.');
    }

    public function show(Assessment $assessment)
    {
        abort_unless($assessment->created_by === Auth::id(), 403);
        $assessment->load(['fields', 'responses.student', 'responses.classSession']);

        return view('instructor.assessments.show', compact('assessment'));
    }

    public function submitResponse(Request $request, Assessment $assessment)
    {
        $data = $request->validate([
            'student_id'       => 'nullable|exists:users,id',
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'responses'        => 'required|array',
            'recommendations'  => 'nullable|string',
        ]);

        $response = AssessmentResponse::create([
            'assessment_id'    => $assessment->id,
            'submitted_by'     => Auth::id(),
            'student_id'       => $data['student_id'] ?? null,
            'class_session_id' => $data['class_session_id'] ?? null,
            'responses'        => $data['responses'],
            'recommendations'  => $data['recommendations'] ?? null,
        ]);

        // Auto-generate instructor tasks from recommendations
        $tasks = $this->taskService->generateTasksFromResponse($response);

        $message = 'Assessment submitted.';
        if (!empty($tasks)) {
            $message .= ' ' . count($tasks) . ' task(s) generated.';
        }

        return redirect()->route('instructor.assessments.show', $assessment)->with('success', $message);
    }
}
