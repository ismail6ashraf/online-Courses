<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speech_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('speaker_id')->constrained('users')->onDelete('cascade');
            $table->longText('transcript');
            $table->string('audio_file')->nullable();
            $table->integer('start_time_seconds')->nullable();
            $table->integer('end_time_seconds')->nullable();
            $table->float('confidence_score')->nullable();
            $table->string('language')->default('ar');
            $table->boolean('analyzed')->default(false);
            $table->timestamps();
        });

        Schema::create('behavior_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('speech_log_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['negative_speech', 'positive_speech', 'motivational'])->default('negative_speech');
            $table->text('detected_phrase');
            $table->text('full_context')->nullable();
            $table->float('sentiment_score')->nullable();
            $table->integer('timestamp_seconds')->nullable();
            $table->boolean('alert_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_incidents');
        Schema::dropIfExists('speech_logs');
    }
};
