<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_gets_notification_when_instructor_creates_assessment(): void
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

        $this->actingAs($instructor)->post(route('instructor.assessments.store'), [
            'title' => 'Homework 1',
            'description' => 'Solve the first exercise.',
            'course_id' => $course->id,
            'type' => 'general',
            'fields' => [
                [
                    'label' => 'Answer',
                    'type' => 'textarea',
                    'required' => true,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('alerts', [
            'target_user_id' => $student->id,
            'triggered_by' => $instructor->id,
            'type' => 'assessment_task',
            'title' => 'New assignment: Homework 1',
            'is_read' => false,
        ]);
    }

    public function test_student_gets_notification_when_course_is_archived(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'PHP Basics',
            'description' => 'Intro to PHP',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $course->students()->attach($student->id);

        $this->actingAs($instructor)->put(route('instructor.courses.update', $course), [
            'title' => $course->title,
            'description' => $course->description,
            'level' => 'Beginner',
            'language' => 'Arabic',
            'duration_hours' => 12,
            'status' => 'archived',
        ])->assertRedirect(route('instructor.courses.index'));

        $this->assertDatabaseHas('alerts', [
            'target_user_id' => $student->id,
            'triggered_by' => $instructor->id,
            'type' => 'system',
            'title' => 'Course completed: PHP Basics',
            'is_read' => false,
        ]);
    }
}
