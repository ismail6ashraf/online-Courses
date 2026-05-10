<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'triggered_by', 'target_user_id', 'class_session_id',
        'type', 'severity', 'title', 'message', 'context',
        'is_read', 'read_at', 'notified_admin',
    ];

    protected function casts(): array
    {
        return [
            'context'        => 'array',
            'is_read'        => 'boolean',
            'read_at'        => 'datetime',
            'notified_admin' => 'boolean',
        ];
    }

    public function triggeredBy() { return $this->belongsTo(User::class, 'triggered_by'); }
    public function targetUser()  { return $this->belongsTo(User::class, 'target_user_id'); }
    public function classSession(){ return $this->belongsTo(ClassSession::class); }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
