<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'class_session_id', 'sender_id', 'message',
        'is_flagged', 'flag_reason', 'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    public function classSession()      { return $this->belongsTo(ClassSession::class); }
    public function sender()            { return $this->belongsTo(User::class, 'sender_id'); }
    public function dataLeakageIncidents() { return $this->hasMany(DataLeakageIncident::class); }
}
