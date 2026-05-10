<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->longText('full_transcript');
            $table->json('word_timestamps')->nullable();
            $table->string('language')->default('ar');
            $table->boolean('is_searchable')->default(true);
            $table->float('processing_duration_seconds')->nullable();
            $table->timestamps();
        });

        Schema::create('training_plan_adherences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->integer('planned_duration_minutes');
            $table->integer('actual_duration_minutes')->nullable();
            $table->float('adherence_percentage')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_plan_adherences');
        Schema::dropIfExists('session_transcripts');
    }
};
