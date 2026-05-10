<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with(['targetUser', 'triggeredBy', 'classSession']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $alerts = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.alerts.index', compact('alerts'));
    }

    public function show(Alert $alert)
    {
        $alert->markAsRead();
        return view('admin.alerts.show', compact('alert'));
    }

    public function markAllRead()
    {
        Alert::where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'All alerts marked as read.');
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();
        return back()->with('success', 'Alert deleted.');
    }
}
