<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeechLog extends Model
{
    protected $fillable = [
        'class_session_id', 'speaker_id', 'transcript', 'audio_file',
        'start_time_seconds', 'end_time_seconds', 'confidence_score', 'language', 'analyzed',
    ];

    protected function casts(): array
    {
        return ['analyzed' => 'boolean'];
    }

    public function classSession()     { return $this->belongsTo(ClassSession::class); }
    public function speaker()          { return $this->belongsTo(User::class, 'speaker_id'); }
    public function behaviorIncidents(){ return $this->hasMany(BehaviorIncident::class); }
}
