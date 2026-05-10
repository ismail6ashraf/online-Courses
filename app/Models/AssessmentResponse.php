<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentResponse extends Model
{
    protected $fillable = [
        'assessment_id', 'submitted_by', 'student_id',
        'class_session_id', 'responses', 'recommendations', 'tasks_generated',
    ];

    protected function casts(): array
    {
        return [
            'responses'       => 'array',
            'tasks_generated' => 'boolean',
        ];
    }

    public function assessment()   { return $this->belongsTo(Assessment::class); }
    public function submitter()    { return $this->belongsTo(User::class, 'submitted_by'); }
    public function student()      { return $this->belongsTo(User::class, 'student_id'); }
    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function tasks()        { return $this->hasMany(InstructorTask::class); }
}
