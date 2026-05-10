<?php

use App\Http\Controllers\Api\SessionMonitorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('sessions/{session}')->name('api.sessions.')->group(function () {
        Route::post('/heartbeat', [SessionMonitorController::class, 'heartbeat'])->name('heartbeat');
        Route::post('/silence', [SessionMonitorController::class, 'reportSilence'])->name('silence');
        Route::post('/transcript', [SessionMonitorController::class, 'submitTranscript'])->name('transcript');
        Route::post('/chat', [SessionMonitorController::class, 'submitChatMessage'])->name('chat');
        Route::get('/stats', [SessionMonitorController::class, 'liveStats'])->name('stats');
        Route::post('/join', [SessionMonitorController::class, 'joinSession'])->name('join');
        Route::post('/leave', [SessionMonitorController::class, 'leaveSession'])->name('leave');
    });
});
