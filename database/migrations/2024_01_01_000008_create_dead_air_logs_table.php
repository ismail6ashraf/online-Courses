<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dead_air_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->integer('start_second');
            $table->integer('end_second')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->enum('status', ['detected', 'resolved', 'ongoing'])->default('detected');
            $table->boolean('alert_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dead_air_logs');
    }
};
