<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'custom', 'session'])->default('daily');
            $table->enum('subject', ['instructor', 'student', 'session', 'overall'])->default('overall');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('class_session_id')->nullable()->constrained()->onDelete('set null');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('data');
            $table->string('file_path')->nullable();
            $table->enum('status', ['generating', 'ready', 'failed'])->default('generating');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
