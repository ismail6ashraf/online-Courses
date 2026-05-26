<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\User;

class StudentNotificationService
{
    public function notifyAssessmentPublished(Assessment $assessment): void
    {
        $assessment->loadMissing(['course.students', 'classSession.course.students', 'creator']);

        $course = $assessment->course ?? $assessment->classSession?->course;

        if (!$course) {
            return;
        }

        foreach ($course->students as $student) {
            Alert::create([
                'triggered_by' => $assessment->created_by,
                'target_user_id' => $student->id,
                'type' => 'assessment_task',
                'severity' => 'info',
                'title' => 'New assignment: ' . $assessment->title,
                'message' => ($assessment->creator?->name ?? 'Your instructor')
                    . ' sent a new assignment for ' . $course->title . '.',
                'context' => [
                    'kind' => 'assessment_published',
                    'assessment_id' => $assessment->id,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                ],
            ]);
        }
    }

    public function notifyCourseCompleted(Course $course, User $instructor): void
    {
        $course->loadMissing('students');

        foreach ($course->students as $student) {
            Alert::create([
                'triggered_by' => $instructor->id,
                'target_user_id' => $student->id,
                'type' => 'system',
                'severity' => 'info',
                'title' => 'Course completed: ' . $course->title,
                'message' => 'You completed ' . $course->title . '. Great work finishing the course.',
                'context' => [
                    'kind' => 'course_completed',
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                ],
            ]);
        }
    }
}
