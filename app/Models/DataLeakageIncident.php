<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLeakageIncident extends Model
{
    protected $fillable = [
        'class_session_id', 'user_id', 'channel', 'detected_content',
        'leakage_type', 'chat_message_id', 'speech_log_id', 'alert_sent',
    ];

    protected function casts(): array
    {
        return ['alert_sent' => 'boolean'];
    }

    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function user()         { return $this->belongsTo(User::class); }
    public function chatMessage()  { return $this->belongsTo(ChatMessage::class); }
    public function speechLog()    { return $this->belongsTo(SpeechLog::class); }
}
