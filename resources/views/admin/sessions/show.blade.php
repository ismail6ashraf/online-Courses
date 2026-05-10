@extends('layouts.app')

@section('title', $session->title)
@section('page-title', 'Session Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5>{{ $session->title }}</h5>
        <small class="text-muted">
            <i class="bi bi-person me-1"></i>{{ $session->instructor->name ?? '—' }}
            | <i class="bi bi-book me-1"></i>{{ $session->course->title ?? '—' }}
            | <i class="bi bi-clock me-1"></i>{{ $session->scheduled_at->format('d M Y H:i') }}
        </small>
    </div>
    <a href="{{ route('admin.sessions.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-primary">{{ $session->attendances->count() }}</div><small class="text-muted">Attendees</small></div></div>
    <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-danger">{{ $session->behaviorIncidents->where('type','negative_speech')->count() }}</div><small class="text-muted">Negative Speech</small></div></div>
    <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-warning">{{ $session->dead_air_seconds }}s</div><small class="text-muted">Dead Air</small></div></div>
    <div class="col-sm-3"><div class="card text-center p-3"><div class="fs-3 fw-bold text-danger">{{ $session->dataLeakageIncidents->count() }}</div><small class="text-muted">Data Leakage</small></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0">Attendance</h6></div>
            <div class="list-group list-group-flush">
                @forelse($session->attendances as $att)
                <div class="list-group-item d-flex justify-content-between">
                    <span class="small">{{ $att->user->name ?? '—' }}</span>
                    <span class="badge bg-{{ $att->status === 'present' ? 'success' : 'danger' }}">{{ ucfirst($att->status) }}</span>
                </div>
                @empty
                <div class="list-group-item text-muted small text-center">No records</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0">Behavior Incidents</h6></div>
            <div class="list-group list-group-flush">
                @forelse($session->behaviorIncidents as $inc)
                <div class="list-group-item">
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $inc->type==='negative_speech'?'danger':'success' }}">{{ $inc->type==='negative_speech'?'NEG':'POS' }}</span>
                        <div>
                            <div class="small fw-medium">{{ $inc->user->name ?? '—' }}</div>
                            <div class="text-muted small">"{{ $inc->detected_phrase }}"</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-muted small text-center">No incidents</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
