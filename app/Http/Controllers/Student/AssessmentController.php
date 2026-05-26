<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function show(Assessment $assessment)
    {
        $this->authorizeAssessment($assessment);

        $assessment->load(['fields', 'course', 'classSession']);

        $response = AssessmentResponse::where('assessment_id', $assessment->id)
            ->where('student_id', auth()->id())
            ->latest()
            ->first();

        return view('student.assessments.show', compact('assessment', 'response'));
    }

    public function submit(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessment($assessment);

        $assessment->load('fields');

        $data = $request->validate([
            'responses' => 'required|array',
        ]);

        foreach ($assessment->fields as $field) {
            if ($field->required && blank($data['responses'][$field->id] ?? null)) {
                return back()
                    ->withErrors(['responses.' . $field->id => $field->label . ' is required.'])
                    ->withInput();
            }
        }

        AssessmentResponse::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => auth()->id(),
            ],
            [
                'submitted_by' => auth()->id(),
                'class_session_id' => $assessment->class_session_id,
                'responses' => $data['responses'],
                'tasks_generated' => false,
            ]
        );

        return redirect()
            ->route('student.assessments.show', $assessment)
            ->with('success', 'Your assessment was submitted successfully.');
    }

    private function authorizeAssessment(Assessment $assessment): void
    {
        abort_unless($assessment->status === 'active', 404);

        $course = $assessment->course ?? $assessment->classSession?->course;

        abort_unless($course, 404);

        abort_unless(
            auth()->user()->enrolledCourses()->where('courses.id', $course->id)->exists(),
            403
        );
    }
}
