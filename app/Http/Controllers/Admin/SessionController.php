<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassSession::with(['course', 'instructor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('instructor')) {
            $query->where('instructor_id', $request->instructor);
        }

        $sessions = $query->orderByDesc('scheduled_at')->paginate(20)->withQueryString();
        $instructors = User::where('role', 'instructor')->get();

        return view('admin.sessions.index', compact('sessions', 'instructors'));
    }

    public function show(ClassSession $session)
    {
        $session->load([
            'course', 'instructor', 'attendances.user',
            'behaviorIncidents.user', 'deadAirLogs',
            'dataLeakageIncidents.user', 'alerts', 'transcript',
        ]);

        return view('admin.sessions.show', compact('session'));
    }

    public function destroy(ClassSession $session)
    {
        $session->delete();
        return redirect()->route('admin.sessions.index')->with('success', 'Session deleted.');
    }
}
