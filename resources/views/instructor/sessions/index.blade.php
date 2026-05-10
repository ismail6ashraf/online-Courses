@extends('layouts.app')

@section('title', 'My Sessions')
@section('page-title', 'My Sessions')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('instructor.sessions.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Session
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>Course</th><th>Scheduled</th><th>Status</th><th>Duration</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-medium">{{ $session->title }}</td>
                    <td class="text-muted small">{{ $session->course->title ?? '—' }}</td>
                    <td class="text-muted small">{{ $session->scheduled_at->format('d M Y H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $session->status === 'live' ? 'danger' : ($session->status === 'completed' ? 'success' : ($session->status === 'scheduled' ? 'primary' : 'secondary')) }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $session->duration_minutes ? $session->duration_minutes.' min' : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($session->status === 'scheduled')
                            <form action="{{ route('instructor.sessions.start', $session) }}" method="POST">
                                @csrf <button class="btn btn-xs btn-sm btn-success">Start</button>
                            </form>
                            @elseif($session->status === 'live')
                            <a href="{{ route('instructor.sessions.live', $session) }}" class="btn btn-xs btn-sm btn-danger">Go Live</a>
                            @endif
                            <a href="{{ route('instructor.sessions.show', $session) }}" class="btn btn-xs btn-sm btn-outline-secondary">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $sessions->links() }}</div>
</div>
@endsection
