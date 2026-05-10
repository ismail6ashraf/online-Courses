<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AssessmentResponse;
use App\Models\InstructorTask;
use Illuminate\Support\Facades\Log;

class AssessmentTaskService
{
    /**
     * Convert assessment recommendations into instructor tasks.
     */
    public function generateTasksFromResponse(AssessmentResponse $response): array
    {
        if ($response->tasks_generated) {
            return [];
        }

        $recommendations = $response->recommendations;

        if (empty($recommendations)) {
            $recommendations = $this->extractRecommendationsFromResponses($response);
        }

        $tasks = $this->parseRecommendations($recommendations, $response);

        $response->update(['tasks_generated' => true]);

        // Send alert to instructor
        if (!empty($tasks)) {
            $this->notifyInstructor($response, count($tasks));
        }

        return $tasks;
    }

    private function extractRecommendationsFromResponses(AssessmentResponse $response): string
    {
        $parts = [];
        foreach ($response->responses as $fieldId => $value) {
            if (is_string($value) && strlen($value) > 20) {
                $parts[] = $value;
            }
        }
        return implode("\n", $parts);
    }

    private function parseRecommendations(string $recommendations, AssessmentResponse $response): array
    {
        if (empty(trim($recommendations))) {
            return [];
        }

        $lines = array_filter(
            explode("\n", $recommendations),
            fn($line) => strlen(trim($line)) > 5
        );

        $tasks = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $priority = $this->detectPriority($line);

            $task = InstructorTask::create([
                'instructor_id'           => $response->classSession?->instructor_id
                    ?? $response->assessment->creator_id ?? null,
                'assessment_response_id'  => $response->id,
                'class_session_id'        => $response->class_session_id,
                'title'                   => mb_substr($line, 0, 100),
                'description'             => $line,
                'priority'                => $priority,
                'status'                  => 'pending',
                'due_at'                  => now()->addDays(3),
            ]);

            $tasks[] = $task;
        }

        return $tasks;
    }

    private function detectPriority(string $text): string
    {
        $urgentKeywords = ['urgent', 'immediately', 'critical', 'عاجل', 'فوري', 'ضروري'];
        $highKeywords   = ['important', 'required', 'مهم', 'مطلوب'];

        $lower = mb_strtolower($text);

        foreach ($urgentKeywords as $keyword) {
            if (mb_strpos($lower, $keyword) !== false) {
                return 'urgent';
            }
        }

        foreach ($highKeywords as $keyword) {
            if (mb_strpos($lower, $keyword) !== false) {
                return 'high';
            }
        }

        return 'medium';
    }

    private function notifyInstructor(AssessmentResponse $response, int $taskCount): void
    {
        $instructorId = $response->classSession?->instructor_id;

        if (!$instructorId) {
            return;
        }

        Alert::create([
            'triggered_by'     => $response->submitted_by,
            'target_user_id'   => $instructorId,
            'class_session_id' => $response->class_session_id,
            'type'             => 'assessment_task',
            'severity'         => 'info',
            'title'            => 'New Tasks from Assessment',
            'message'          => "{$taskCount} new task(s) have been generated from the student assessment. Please review them before the next session.",
            'context'          => ['response_id' => $response->id, 'task_count' => $taskCount],
        ]);
    }
}
