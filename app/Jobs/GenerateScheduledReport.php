<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ReportGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateScheduledReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;

    public function __construct(
        public string $type,   // daily, weekly, monthly
        public int $adminId,
    ) {}

    public function handle(ReportGeneratorService $generator): void
    {
        $admin = User::findOrFail($this->adminId);

        match($this->type) {
            'daily'   => $generator->generateDailyReport($admin),
            'weekly'  => $generator->generateWeeklyReport($admin),
            'monthly' => $generator->generateMonthlyReport($admin),
        };
    }
}
