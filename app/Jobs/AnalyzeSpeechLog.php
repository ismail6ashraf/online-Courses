<?php

namespace App\Jobs;

use App\Models\SpeechLog;
use App\Services\DataLeakagePreventionService;
use App\Services\SpeechAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeSpeechLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public SpeechLog $speechLog) {}

    public function handle(SpeechAnalyzerService $speechAnalyzer, DataLeakagePreventionService $dlpService): void
    {
        if ($this->speechLog->analyzed) {
            return;
        }

        $speechAnalyzer->processSpeechLog($this->speechLog);
        $dlpService->scanSpeechLog($this->speechLog);
    }
}
