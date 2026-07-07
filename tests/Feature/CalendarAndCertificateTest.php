<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarAndCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_calendar_renders_sessions_and_assignments(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        $course = Course::create([
            'title' => 'Laravel Pro',
            'description' => 'Advanced Laravel',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        ClassSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Service Container',
            'scheduled_at' => now()->addDays(2),
            'status' => 'scheduled',
        ]);

        CourseAssignment::create([
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'title' => 'Container Homework',
            'due_at' => now()->addDays(3),
            'points' => 100,
            'is_published' => true,
        ]);

        $this->actingAs($instructor)
            ->get(route('instructor.calendar'))
            ->assertOk()
            ->assertSee('Teaching Calendar')
            ->assertSee('Service Container')
            ->assertSee('Container Homework');
    }

    public function test_student_calendar_and_certificate_render_for_completed_course(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'title' => 'PHP Foundations',
            'description' => 'Core PHP',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $course->students()->attach($student->id, ['status' => 'completed']);

        CourseAssignment::create([
            'course_id' => $course->id,
            'created_by' => $instructor->id,
            'title' => 'Final Practice',
            'due_at' => now()->addDay(),
            'points' => 100,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.calendar'))
            ->assertOk()
            ->assertSee('My Calendar')
            ->assertSee('Final Practice');

        $this->actingAs($student)
            ->get(route('student.courses.certificate', $course))
            ->assertOk()
            ->assertSee('Certificate of Completion')
            ->assertSee('PHP Foundations')
            ->assertSee($student->name);
    }
}
