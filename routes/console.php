<?php

use App\Jobs\GenerateScheduledReport;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily report at 11:59 PM
Schedule::call(function () {
    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        GenerateScheduledReport::dispatch('daily', $admin->id);
    }
})->dailyAt('23:59')->name('generate-daily-report');

// Weekly report every Sunday at midnight
Schedule::call(function () {
    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        GenerateScheduledReport::dispatch('weekly', $admin->id);
    }
})->weeklyOn(0, '00:00')->name('generate-weekly-report');

// Monthly report on the 1st of each month
Schedule::call(function () {
    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        GenerateScheduledReport::dispatch('monthly', $admin->id);
    }
})->monthlyOn(1, '01:00')->name('generate-monthly-report');
