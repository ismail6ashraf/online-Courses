<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorIncident extends Model
{
    protected $fillable = [
        'class_session_id', 'user_id', 'speech_log_id', 'type',
        'detected_phrase', 'full_context', 'sentiment_score', 'timestamp_seconds', 'alert_sent',
    ];

    protected function casts(): array
    {
        return ['alert_sent' => 'boolean'];
    }

    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function user()         { return $this->belongsTo(User::class); }
    public function speechLog()    { return $this->belongsTo(SpeechLog::class); }
}
