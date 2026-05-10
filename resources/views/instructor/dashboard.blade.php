@extends('layouts.app')

@section('title', 'Instructor Dashboard')
@section('page-title', 'My Dashboard')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">My Courses</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_courses'] }}</div>
                </div>
                <i class="bi bi-book fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#0284c7)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Upcoming Sessions</div>
                    <div class="fs-3 fw-bold">{{ $stats['upcoming_sessions'] }}</div>
                </div>
                <i class="bi bi-calendar-event fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Pending Tasks</div>
                    <div class="fs-3 fw-bold">{{ $stats['pending_tasks'] }}</div>
                </div>
                <i class="bi bi-list-task fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#dc2626,#ef4444)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Unread Alerts</div>
                    <div class="fs-3 fw-bold">{{ $stats['unread_alerts'] }}</div>
                </div>
                <i class="bi bi-bell fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Upcoming Sessions --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Upcoming Sessions</h6>
                <a href="{{ route('instructor.sessions.create') }}" class="btn btn-sm btn-primary">+ New Session</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingSessions as $session)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-medium small">{{ $session->title }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                <i class="bi bi-book me-1"></i>{{ $session->course->title ?? '—' }}
                            </div>
                            <div class="text-primary" style="font-size:.75rem">
                                <i class="bi bi-clock me-1"></i>{{ $session->scheduled_at->format('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('instructor.sessions.show', $session) }}" class="btn btn-xs btn-sm btn-outline-secondary">View</a>
                            <form action="{{ route('instructor.sessions.start', $session) }}" method="POST">
                                @csrf
                                <button class="btn btn-xs btn-sm btn-success">Start</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted small py-4">No upcoming sessions</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pending Tasks --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Tasks Before Next Session</h6>
                <a href="{{ route('instructor.tasks.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($pendingTasks as $task)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-2">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning text-dark' : 'secondary') }} small">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="small fw-medium">{{ $task->title }}</span>
                            </div>
                            @if($task->assessmentResponse?->student)
                            <div class="text-muted" style="font-size:.72rem">
                                <i class="bi bi-person me-1"></i>{{ $task->assessmentResponse->student->name }}
                            </div>
                            @endif
                            @if($task->due_at)
                            <div class="{{ $task->due_at->isPast() ? 'text-danger' : 'text-muted' }}" style="font-size:.72rem">
                                <i class="bi bi-calendar me-1"></i>Due: {{ $task->due_at->format('d M Y') }}
                            </div>
                            @endif
                        </div>
                        <form action="{{ route('instructor.tasks.status', $task) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button class="btn btn-xs btn-sm btn-outline-success" title="Mark complete">
                                <i class="bi bi-check"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted small py-4">No pending tasks</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
