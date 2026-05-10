<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'class_session_id', 'user_id', 'status',
        'joined_at', 'left_at', 'duration_minutes', 'ip_address', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at'   => 'datetime',
        ];
    }

    public function session() { return $this->belongsTo(ClassSession::class, 'class_session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
}
