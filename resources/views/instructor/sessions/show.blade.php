@extends('layouts.app')

@section('title', $session->title)
@section('page-title', 'Session Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5>{{ $session->title }}</h5>
        <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $session->scheduled_at->format('d M Y H:i') }}</small>
    </div>
    <div class="d-flex gap-2">
        @if($session->status === 'scheduled')
        <form action="{{ route('instructor.sessions.start', $session) }}" method="POST">
            @csrf <button class="btn btn-sm btn-success"><i class="bi bi-play-fill me-1"></i>Start Session</button>
        </form>
        @elseif($session->status === 'live')
        <a href="{{ route('instructor.sessions.live', $session) }}" class="btn btn-sm btn-danger">
            <i class="bi bi-circle-fill me-1"></i>Go Live
        </a>
        @endif
        <a href="{{ route('instructor.sessions.edit', $session) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-primary">{{ $session->attendances->count() }}</div><small class="text-muted">Students</small></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-warning">{{ $session->dead_air_seconds }}s</div><small class="text-muted">Dead Air</small></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-danger">{{ $session->behaviorIncidents->count() }}</div><small class="text-muted">Incidents</small></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-primary">{{ $session->instructorTasks->count() }}</div><small class="text-muted">Tasks</small></div></div>
</div>

@if($session->instructorTasks->isNotEmpty())
<div class="card mt-3">
    <div class="card-header bg-transparent"><h6 class="mb-0">Pre-Session Tasks</h6></div>
    <div class="list-group list-group-flush">
        @foreach($session->instructorTasks as $task)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : 'warning text-dark' }} me-2">{{ ucfirst($task->priority) }}</span>
                <span class="small">{{ $task->title }}</span>
            </div>
            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : 'secondary' }}">{{ ucfirst($task->status) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
