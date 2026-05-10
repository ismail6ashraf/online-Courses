<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorTask extends Model
{
    protected $fillable = [
        'instructor_id', 'assessment_response_id', 'class_session_id',
        'title', 'description', 'priority', 'status', 'due_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at'       => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function instructor()        { return $this->belongsTo(User::class, 'instructor_id'); }
    public function assessmentResponse(){ return $this->belongsTo(AssessmentResponse::class); }
    public function classSession()      { return $this->belongsTo(ClassSession::class); }
}
