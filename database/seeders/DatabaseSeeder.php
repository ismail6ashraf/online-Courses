<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentField;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\InstructorTask;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@online-classroom.com'],
            [
                'name'      => 'System Admin',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        // Instructors
        $instructor1 = User::firstOrCreate(
            ['email' => 'instructor1@online-classroom.com'],
            [
                'name'      => 'Ahmed Hassan',
                'password'  => Hash::make('password123'),
                'role'      => 'instructor',
                'specialty' => 'Mathematics',
                'phone'     => '0501234567',
                'is_active' => true,
            ]
        );

        $instructor2 = User::firstOrCreate(
            ['email' => 'instructor2@online-classroom.com'],
            [
                'name'      => 'Sara Al-Rashidi',
                'password'  => Hash::make('password123'),
                'role'      => 'instructor',
                'specialty' => 'English Language',
                'phone'     => '0509876543',
                'is_active' => true,
            ]
        );

        // Students
        $students = [];

        foreach (
            [
                ['Mohammed Ali', 'student1@online-classroom.com'],
                ['Fatima Al-Zahrani', 'student2@online-classroom.com'],
                ['Omar Al-Otaibi', 'student3@online-classroom.com'],
                ['Noura Al-Shehri', 'student4@online-classroom.com'],
            ] as [$name, $email]
        ) {

            $students[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'is_active' => true
                ]
            );
        }

        // Courses
        $course1 = Course::firstOrCreate(
            [
                'title' => 'Advanced Mathematics',
                'instructor_id' => $instructor1->id
            ],
            [
                'description' => 'Advanced mathematics covering calculus and linear algebra.',
                'level' => 'Advanced',
                'language' => 'Arabic',
                'duration_hours' => 40,
                'status' => 'active'
            ]
        );

        $course2 = Course::firstOrCreate(
            [
                'title' => 'Business English',
                'instructor_id' => $instructor2->id
            ],
            [
                'description' => 'Business English communication skills.',
                'level' => 'Intermediate',
                'language' => 'English',
                'duration_hours' => 30,
                'status' => 'active'
            ]
        );

        foreach ($students as $student) {
            $course1->students()->syncWithoutDetaching([$student->id]);
            $course2->students()->syncWithoutDetaching([$student->id]);
        }

        // Sessions
        $session1 = ClassSession::firstOrCreate(
            [
                'title' => 'Session 1: Introduction to Calculus',
                'course_id' => $course1->id
            ],
            [
                'instructor_id' => $instructor1->id,
                'description' => 'Limits and derivatives basics.',
                'scheduled_at' => now()->addDays(2)->setTime(10, 0),
                'status' => 'scheduled',
                'platform' => 'Zoom'
            ]
        );

        $session2 = ClassSession::firstOrCreate(
            [
                'title' => 'Session 2: Email Writing Skills',
                'course_id' => $course2->id
            ],
            [
                'instructor_id' => $instructor2->id,
                'description' => 'Professional email structures.',
                'scheduled_at' => now()->addDays(3)->setTime(14, 0),
                'status' => 'scheduled',
                'platform' => 'Teams'
            ]
        );

        // Assessment
        $assessment = Assessment::firstOrCreate(
            [
                'title' => 'Post-Session Student Evaluation',
                'created_by' => $instructor1->id
            ],
            [
                'description' => 'Evaluate student understanding after each session.',
                'course_id' => $course1->id,
                'type' => 'post_session',
                'status' => 'active'
            ]
        );

        if ($assessment->wasRecentlyCreated) {

            $fields = [
                [
                    'label' => 'Did the student participate actively?',
                    'type' => 'rating',
                    'required' => true,
                    'options' => null
                ],
                [
                    'label' => 'Student understanding level',
                    'type' => 'select',
                    'required' => true,
                    'options' => ['Excellent', 'Good', 'Average', 'Below Average']
                ],
                [
                    'label' => 'Specific challenges observed',
                    'type' => 'textarea',
                    'required' => false,
                    'options' => null
                ],
                [
                    'label' => 'Recommendations for next session',
                    'type' => 'textarea',
                    'required' => false,
                    'options' => null
                ],
            ];

            foreach ($fields as $i => $field) {
                AssessmentField::create(
                    array_merge(
                        $field,
                        [
                            'assessment_id' => $assessment->id,
                            'sort_order' => $i
                        ]
                    )
                );
            }
        }

        // Instructor Tasks
        InstructorTask::firstOrCreate(
            [
                'title' => 'Prepare extra practice problems for calculus',
                'instructor_id' => $instructor1->id
            ],
            [
                'description' => 'Student needs additional exercises on limits. Prepare 10 practice problems.',
                'priority' => 'high',
                'status' => 'pending',
                'class_session_id' => $session1->id,
                'due_at' => now()->addDay()
            ]
        );

        InstructorTask::firstOrCreate(
            [
                'title' => 'Review email templates for next session',
                'instructor_id' => $instructor2->id
            ],
            [
                'description' => 'Prepare real-world email templates for the business English session.',
                'priority' => 'medium',
                'status' => 'pending',
                'class_session_id' => $session2->id,
                'due_at' => now()->addDays(2)
            ]
        );

        // Plans Seeder
        $this->call([
            PlanSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->line('Login credentials:');
        $this->command->line('Admin: admin@online-classroom.com / password123');
        $this->command->line('Instructor: instructor1@online-classroom.com / password123');
        $this->command->line('Student: student1@online-classroom.com / password123');
    }
}
