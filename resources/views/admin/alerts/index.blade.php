@extends('layouts.app')

@section('title', 'Alerts')
@section('page-title', 'System Alerts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <select name="type" class="form-select form-select-sm">
            <option value="">All Types</option>
            <option value="negative_speech" {{ request('type')=='negative_speech'?'selected':'' }}>Negative Speech</option>
            <option value="dead_air" {{ request('type')=='dead_air'?'selected':'' }}>Dead Air</option>
            <option value="data_leakage_chat" {{ request('type')=='data_leakage_chat'?'selected':'' }}>Data Leakage (Chat)</option>
            <option value="data_leakage_voice" {{ request('type')=='data_leakage_voice'?'selected':'' }}>Data Leakage (Voice)</option>
            <option value="assessment_task" {{ request('type')=='assessment_task'?'selected':'' }}>Assessment Task</option>
        </select>
        <select name="severity" class="form-select form-select-sm">
            <option value="">All Severity</option>
            <option value="critical" {{ request('severity')=='critical'?'selected':'' }}>Critical</option>
            <option value="warning" {{ request('severity')=='warning'?'selected':'' }}>Warning</option>
            <option value="info" {{ request('severity')=='info'?'selected':'' }}>Info</option>
        </select>
        <div class="form-check d-flex align-items-center gap-1">
            <input type="checkbox" name="unread_only" value="1" class="form-check-input" {{ request('unread_only')?'checked':'' }}>
            <label class="form-check-label small">Unread only</label>
        </div>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
    <form action="{{ route('admin.alerts.mark-all-read') }}" method="POST">
        @csrf
        <button class="btn btn-sm btn-outline-primary">Mark All Read</button>
    </form>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse($alerts as $alert)
        <a href="{{ route('admin.alerts.show', $alert) }}"
           class="list-group-item list-group-item-action alert-severity-{{ $alert->severity }} {{ !$alert->is_read ? 'bg-light' : '' }}">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="d-flex gap-3 flex-grow-1 min-w-0">
                    <div class="mt-1">
                        @switch($alert->type)
                            @case('negative_speech') <i class="bi bi-chat-square-dots-fill text-danger fs-5"></i> @break
                            @case('dead_air') <i class="bi bi-mic-mute-fill text-warning fs-5"></i> @break
                            @case('data_leakage_chat')
                            @case('data_leakage_voice') <i class="bi bi-shield-exclamation-fill text-danger fs-5"></i> @break
                            @default <i class="bi bi-bell-fill text-primary fs-5"></i>
                        @endswitch
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2">
                            <strong class="small">{{ $alert->title }}</strong>
                            <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning text-dark' : 'info') }} small">
                                {{ ucfirst($alert->severity) }}
                            </span>
                            @if(!$alert->is_read)<span class="badge bg-primary small">New</span>@endif
                        </div>
                        <div class="text-muted small text-truncate">{{ $alert->message }}</div>
                        @if($alert->targetUser)
                        <div class="text-muted" style="font-size:1.2rem">
                            <i class="bi bi-person me-1"></i>{{ $alert->targetUser->name }}
                            @if($alert->classSession)
                            — <i class="bi bi-camera-video me-1"></i>{{ $alert->classSession->title }}
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                <small class="text-muted text-nowrap">{{ $alert->created_at->diffForHumans() }}</small>
            </div>
        </a>
        @empty
        <div class="list-group-item text-center text-muted py-4">No alerts found.</div>
        @endforelse
    </div>
    <div class="card-footer">{{ $alerts->links() }}</div>
</div>
@endsection
