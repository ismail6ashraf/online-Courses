<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('class_session_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', [
                'negative_speech',
                'positive_speech',
                'dead_air',
                'data_leakage_chat',
                'data_leakage_voice',
                'assessment_task',
                'attendance',
                'system',
            ])->default('system');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('notified_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
