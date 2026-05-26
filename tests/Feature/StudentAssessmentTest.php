<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_open_assessment(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'Laravel',
            'description' => 'Laravel course',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $course->students()->attach($student->id);

        $assessment = Assessment::create([
            'title' => 'Quiz 1',
            'created_by' => $instructor->id,
            'course_id' => $course->id,
            'type' => 'general',
            'status' => 'active',
        ]);

        $assessment->fields()->create([
            'label' => 'What is Laravel?',
            'type' => 'textarea',
            'required' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($student)
            ->get(route('student.assessments.show', $assessment))
            ->assertOk()
            ->assertSee('Quiz 1')
            ->assertSee('What is Laravel?');
    }

    public function test_enrolled_student_can_submit_assessment_response(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'PHP',
            'description' => 'PHP course',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $course->students()->attach($student->id);

        $assessment = Assessment::create([
            'title' => 'PHP Quiz',
            'created_by' => $instructor->id,
            'course_id' => $course->id,
            'type' => 'general',
            'status' => 'active',
        ]);

        $field = $assessment->fields()->create([
            'label' => 'Answer',
            'type' => 'text',
            'required' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($student)
            ->post(route('student.assessments.submit', $assessment), [
                'responses' => [
                    $field->id => 'My answer',
                ],
            ])
            ->assertRedirect(route('student.assessments.show', $assessment));

        $this->assertDatabaseHas('assessment_responses', [
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'submitted_by' => $student->id,
        ]);
    }
}
