<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'price' => 0.00,
                'duration_days' => 30,
                'max_courses' => 5,
                'max_students' => 25,
                'features' => [
                    '2 courses',
                    '25 students',
                    'Community support',
                ],
            ],
            [
                'name' => 'Pro',
                'price' => 19.00,
                'duration_days' => 30,
                'max_courses' => 20,
                'max_students' => 1000,
                'features' => [
                    '20 courses',
                    '1000 students',
                    'Priority support',
                ],
            ],
            [
                'name' => 'Business',
                'price' => 49.00,
                'duration_days' => 30,
                'max_courses' => null,
                'max_students' => null,
                'features' => [
                    'Unlimited courses',
                    'Unlimited students',
                    'Premium support',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
