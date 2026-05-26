<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Alert;

class NotificationController extends Controller
{
    public function markAllRead()
    {
        Alert::where('target_user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Notifications marked as read.');
    }
}
