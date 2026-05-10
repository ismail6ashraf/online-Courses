<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'title', 'type', 'subject', 'generated_by', 'subject_user_id',
        'class_session_id', 'period_start', 'period_end', 'data', 'file_path', 'status',
    ];

    protected function casts(): array
    {
        return [
            'data'         => 'array',
            'period_start' => 'date',
            'period_end'   => 'date',
        ];
    }

    public function generatedBy()   { return $this->belongsTo(User::class, 'generated_by'); }
    public function subjectUser()   { return $this->belongsTo(User::class, 'subject_user_id'); }
    public function classSession()  { return $this->belongsTo(ClassSession::class); }
}
