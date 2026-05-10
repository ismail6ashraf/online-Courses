<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\InstructorTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = InstructorTask::where('instructor_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->with(['classSession', 'assessmentResponse.student'])
            ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
            ->orderBy('due_at')
            ->paginate(20)
            ->withQueryString();

        return view('instructor.tasks.index', compact('tasks'));
    }

    public function updateStatus(Request $request, InstructorTask $task)
    {
        abort_unless($task->instructor_id === Auth::id(), 403);

        $request->validate(['status' => 'required|in:pending,in_progress,completed,dismissed']);

        $data = ['status' => $request->status];

        if ($request->status === 'completed') {
            $data['completed_at'] = now();
        }

        $task->update($data);

        return back()->with('success', 'Task status updated.');
    }
}
