<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ending_a_live_session_generates_a_session_report(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => 'Math',
            'description' => 'Math course',
            'instructor_id' => $instructor->id,
            'status' => 'active',
        ]);

        $session = ClassSession::create([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'title' => 'Live math session',
            'scheduled_at' => now()->subHour(),
            'started_at' => now()->subMinutes(45),
            'status' => 'live',
            'platform' => 'Zoom',
        ]);

        $response = $this->actingAs($instructor)
            ->post(route('instructor.sessions.end', $session));

        $response->assertRedirect(route('instructor.sessions.show', $session));

        $this->assertDatabaseHas('class_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('reports', [
            'type' => 'session',
            'subject' => 'session',
            'class_session_id' => $session->id,
            'generated_by' => $instructor->id,
            'status' => 'ready',
        ]);

        $report = Report::where('class_session_id', $session->id)->firstOrFail();

        $this->assertSame('healthy', $report->data['summary']['overall_status']);
    }
}
