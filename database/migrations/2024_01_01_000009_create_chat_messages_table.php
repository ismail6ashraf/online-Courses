<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('data_leakage_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('channel', ['chat', 'voice'])->default('chat');
            $table->text('detected_content');
            $table->enum('leakage_type', ['phone_number', 'email', 'social_media', 'other'])->default('phone_number');
            $table->foreignId('chat_message_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('speech_log_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('alert_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_leakage_incidents');
        Schema::dropIfExists('chat_messages');
    }
};
