@extends('layouts.app')

@section('title', 'My Tasks')
@section('page-title', 'Pre-Session Tasks')

@section('content')
<div class="d-flex gap-2 mb-3">
    <form class="d-flex gap-2" method="GET">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
            <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
            <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
        </select>
        <select name="priority" class="form-select form-select-sm">
            <option value="">All Priority</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse($tasks as $task)
        <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1 me-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning text-dark' : 'secondary') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                        <span class="fw-medium small">{{ $task->title }}</span>
                        <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'secondary') }}">
                            {{ ucfirst(str_replace('_',' ',$task->status)) }}
                        </span>
                    </div>
                    <p class="text-muted small mb-1">{{ $task->description }}</p>
                    <div class="d-flex gap-3 text-muted" style="font-size:.72rem">
                        @if($task->assessmentResponse?->student)
                        <span><i class="bi bi-person me-1"></i>{{ $task->assessmentResponse->student->name }}</span>
                        @endif
                        @if($task->classSession)
                        <span><i class="bi bi-camera-video me-1"></i>{{ $task->classSession->title }}</span>
                        @endif
                        @if($task->due_at)
                        <span class="{{ $task->due_at->isPast() && $task->status !== 'completed' ? 'text-danger fw-medium' : '' }}">
                            <i class="bi bi-calendar me-1"></i>Due: {{ $task->due_at->format('d M Y') }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    @if($task->status !== 'completed')
                    <form action="{{ route('instructor.tasks.status', $task) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button class="btn btn-xs btn-sm btn-outline-success" title="Mark complete">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </form>
                    @endif
                    @if($task->status === 'pending')
                    <form action="{{ route('instructor.tasks.status', $task) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button class="btn btn-xs btn-sm btn-outline-primary" title="Start working">
                            <i class="bi bi-play"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="list-group-item text-center text-muted py-4">No tasks found.</div>
        @endforelse
    </div>
    <div class="card-footer">{{ $tasks->links() }}</div>
</div>
@endsection
