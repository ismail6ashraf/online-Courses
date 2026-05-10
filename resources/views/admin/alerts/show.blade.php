@extends('layouts.app')

@section('title', $alert->title)
@section('page-title', 'Alert Detail')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card alert-severity-{{ $alert->severity }}">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $alert->title }}</h5>
                        <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning text-dark' : 'info') }}">
                            {{ ucfirst($alert->severity) }}
                        </span>
                        <span class="badge bg-secondary ms-1">{{ ucwords(str_replace('_', ' ', $alert->type)) }}</span>
                    </div>
                    <a href="{{ route('admin.alerts.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
                </div>

                <p class="text-muted">{{ $alert->message }}</p>

                <div class="row g-3 mt-1">
                    @if($alert->targetUser)
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Target User</small>
                            <strong>{{ $alert->targetUser->name }}</strong>
                            <div class="text-muted small">{{ $alert->targetUser->email }}</div>
                        </div>
                    </div>
                    @endif
                    @if($alert->classSession)
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Session</small>
                            <strong>{{ $alert->classSession->title }}</strong>
                            <div class="text-muted small">{{ $alert->classSession->scheduled_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($alert->context)
                <div class="mt-3">
                    <h6>Context Data</h6>
                    <pre class="bg-light p-3 rounded small">{{ json_encode($alert->context, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif

                <div class="mt-3 text-muted small">
                    <i class="bi bi-clock me-1"></i>{{ $alert->created_at->format('d M Y H:i:s') }}
                    @if($alert->read_at)
                    | Read at {{ $alert->read_at->format('d M Y H:i:s') }}
                    @endif
                </div>

                <div class="mt-4">
                    <form action="{{ route('admin.alerts.destroy', $alert) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this alert?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete Alert</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
