<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AttendanceLog;

class ClassSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id', 'instructor_id', 'title', 'description',
        'scheduled_at', 'started_at', 'ended_at', 'duration_minutes',
        'status', 'meeting_url', 'meeting_id', 'meeting_password',
        'platform', 'dead_air_seconds', 'recording_enabled', 'recording_url',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at'   => 'datetime',
            'ended_at'     => 'datetime',
            'recording_enabled' => 'boolean',
        ];
    }

    public function course()      { return $this->belongsTo(Course::class); }
    public function instructor()  { return $this->belongsTo(User::class, 'instructor_id'); }
    public function speechLogs()  { return $this->hasMany(SpeechLog::class); }
    public function chatMessages(){ return $this->hasMany(ChatMessage::class); }
    public function deadAirLogs() { return $this->hasMany(DeadAirLog::class); }
    public function behaviorIncidents() { return $this->hasMany(BehaviorIncident::class); }
    public function dataLeakageIncidents() { return $this->hasMany(DataLeakageIncident::class); }
    public function transcript()  { return $this->hasOne(SessionTranscript::class); }
    public function alerts()      { return $this->hasMany(Alert::class); }
    public function assessments() { return $this->hasMany(Assessment::class); }
    public function instructorTasks() { return $this->hasMany(InstructorTask::class); }

    public function isLive(): bool { return $this->status === 'live'; }
    public function attendances() { return $this->hasMany(AttendanceLog::class, 'class_session_id'); }
}
