@extends('layouts.app')

@section('title', 'Sessions')
@section('page-title', 'All Sessions')

@section('content')
<div class="d-flex gap-2 mb-3">
    <form class="d-flex gap-2" method="GET">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="scheduled" {{ request('status')=='scheduled'?'selected':'' }}>Scheduled</option>
            <option value="live" {{ request('status')=='live'?'selected':'' }}>Live</option>
            <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
            <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
        </select>
        <select name="instructor" class="form-select form-select-sm">
            <option value="">All Instructors</option>
            @foreach($instructors as $inst)
            <option value="{{ $inst->id }}" {{ request('instructor')==$inst->id?'selected':'' }}>{{ $inst->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Instructor</th>
                    <th>Scheduled</th>
                    <th>Status</th>
                    <th>Dead Air</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-medium">{{ $session->title }}</td>
                    <td class="text-muted small">{{ $session->instructor->name ?? '—' }}</td>
                    <td class="text-muted small">{{ $session->scheduled_at->format('d M Y H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $session->status === 'live' ? 'danger' : ($session->status === 'completed' ? 'success' : ($session->status === 'scheduled' ? 'primary' : 'secondary')) }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $session->dead_air_seconds }}s</td>
                    <td>
                        <a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $sessions->links() }}</div>
</div>
@endsection
