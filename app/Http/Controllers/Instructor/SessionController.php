<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = ClassSession::where('instructor_id', Auth::id())
            ->with('course')
            ->orderByDesc('scheduled_at')
            ->paginate(15);

        return view('instructor.sessions.index', compact('sessions'));
    }

    public function create()
    {
        $courses = Course::where('instructor_id', Auth::id())->where('status', 'active')->get();
        return view('instructor.sessions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'           => 'required|exists:courses,id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'scheduled_at'        => 'required|date|after:now',
            'platform'            => 'nullable|string|max:100',
            'meeting_url'         => 'nullable|url',
            'recording_enabled'   => 'boolean',
        ]);

        $data['instructor_id'] = Auth::id();

        $session = ClassSession::create($data);

        return redirect()->route('instructor.sessions.show', $session)
            ->with('success', 'Session created.');
    }

    public function show(ClassSession $session)
    {
        $this->authorizeSession($session);

        $session->load([
            'course',
            'attendances.user',
            'behaviorIncidents',
            'deadAirLogs',
            'instructorTasks',
        ]);

        return view('instructor.sessions.show', compact('session'));
    }

    public function edit(ClassSession $session)
    {
        $this->authorizeSession($session);
        $courses = Course::where('instructor_id', Auth::id())->where('status', 'active')->get();
        return view('instructor.sessions.edit', compact('session', 'courses'));
    }

    public function update(Request $request, ClassSession $session)
    {
        $this->authorizeSession($session);

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'scheduled_at'      => 'required|date',
            'platform'          => 'nullable|string|max:100',
            'meeting_url'       => 'nullable|url',
            'recording_enabled' => 'boolean',
        ]);

        $session->update($data);

        return redirect()->route('instructor.sessions.show', $session)->with('success', 'Session updated.');
    }

    public function start(ClassSession $session)
    {
        $this->authorizeSession($session);

        $session->update([
            'status'     => 'live',
            'started_at' => now(),
        ]);

        return redirect()->route('instructor.sessions.live', $session);
    }

    public function end(ClassSession $session)
    {
        $this->authorizeSession($session);

        $duration = $session->started_at
            ? (int) $session->started_at->diffInMinutes(now())
            : null;

        $session->update([
            'status'           => 'completed',
            'ended_at'         => now(),
            'duration_minutes' => $duration,
        ]);

        return redirect()->route('instructor.sessions.show', $session)->with('success', 'Session ended.');
    }

    public function live(ClassSession $session)
    {
        $this->authorizeSession($session);
        abort_unless($session->isLive(), 404);

        return view('instructor.sessions.live', compact('session'));
    }

    private function authorizeSession(ClassSession $session): void
    {
        abort_unless($session->instructor_id === Auth::id(), 403);
    }

    public function destroy(ClassSession $session)
    {
        $session->delete();

        return redirect()
            ->route('instructor.sessions.index')
            ->with('success', 'Session deleted successfully.');
    }
}
