<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTranscript extends Model
{
    protected $fillable = [
        'class_session_id', 'full_transcript', 'word_timestamps',
        'language', 'is_searchable', 'processing_duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'word_timestamps' => 'array',
            'is_searchable'   => 'boolean',
        ];
    }

    public function classSession() { return $this->belongsTo(ClassSession::class); }
}
