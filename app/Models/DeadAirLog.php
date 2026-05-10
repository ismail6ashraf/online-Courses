<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeadAirLog extends Model
{
    protected $fillable = [
        'class_session_id', 'start_second', 'end_second',
        'duration_seconds', 'status', 'alert_sent',
    ];

    protected function casts(): array
    {
        return ['alert_sent' => 'boolean'];
    }

    public function classSession() { return $this->belongsTo(ClassSession::class); }
}
