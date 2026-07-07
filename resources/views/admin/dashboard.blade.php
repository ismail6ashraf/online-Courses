@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Instructors</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_instructors'] }}</div>
                </div>
                <i class="bi bi-person-badge fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#0284c7)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Students</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_students'] }}</div>
                </div>
                <i class="bi bi-people fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#059669,#10b981)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Total Sessions</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_sessions'] }}</div>
                </div>
                <i class="bi bi-camera-video fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#dc2626,#ef4444)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Live Now</div>
                    <div class="fs-3 fw-bold">{{ $stats['live_sessions'] }}</div>
                </div>
                <span class="position-relative">
                    <i class="bi bi-circle-fill fs-2 opacity-50" style="color:#fff"></i>
                    @if($stats['live_sessions'] > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-light text-danger" style="font-size:.5rem">LIVE</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#f59e0b)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Unread Alerts</div>
                    <div class="fs-3 fw-bold">{{ $stats['unread_alerts'] }}</div>
                </div>
                <i class="bi bi-bell fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small opacity-75 mb-1">Today's Sessions</div>
                    <div class="fs-3 fw-bold">{{ $stats['today_sessions'] }}</div>
                </div>
                <i class="bi bi-calendar-day fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">Active Courses</div>
                    <div class="fs-4 fw-bold">{{ $stats['active_courses'] }}</div>
                </div>
                <i class="bi bi-book text-primary fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">Active Subscriptions</div>
                    <div class="fs-4 fw-bold">{{ $stats['active_subscriptions'] }}</div>
                </div>
                <i class="bi bi-award text-success fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">Pending Payments</div>
                    <div class="fs-4 fw-bold">{{ $stats['pending_payments'] }}</div>
                </div>
                <i class="bi bi-hourglass-split text-warning fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">Approved Revenue</div>
                    <div class="fs-4 fw-bold">${{ number_format($stats['approved_revenue'], 2) }}</div>
                </div>
                <i class="bi bi-cash-coin text-success fs-3"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Weekly Chart --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pt-3">
                <h6 class="mb-0 fw-semibold">Weekly Activity</h6>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pt-3">
                <h6 class="mb-0 fw-semibold">Session Status Mix</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" height="210"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Behavior Incidents --}}
    <div class="col-lg-12">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">Recent Behavior Incidents</h6>
            </div>
            <div class="card-body p-0">
                @forelse($recentIncidents as $incident)
                <div class="d-flex align-items-start gap-3 p-3 border-bottom">
                    <span class="badge {{ $incident->type === 'negative_speech' ? 'bg-danger' : 'bg-success' }} rounded-pill mt-1">
                        {{ $incident->type === 'negative_speech' ? 'NEG' : 'POS' }}
                    </span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="small fw-medium text-truncate">{{ $incident->user->name ?? 'Unknown' }}</div>
                        <div class="text-muted small text-truncate">"{{ $incident->detected_phrase }}"</div>
                        <div class="text-muted" style="font-size:.7rem">{{ $incident->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="p-3 text-muted small text-center">No incidents recorded</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Unread Alerts --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Recent Alerts</h6>
                <a href="{{ route('admin.alerts.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentAlerts as $alert)
                <a href="{{ route('admin.alerts.show', $alert) }}"
                   class="list-group-item list-group-item-action alert-severity-{{ $alert->severity }} d-flex gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between">
                            <small class="fw-semibold">{{ $alert->title }}</small>
                            <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted text-truncate d-block">{{ $alert->message }}</small>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted small text-center">No alerts</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Data Leakage Incidents --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0 bg-transparent pt-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-shield-exclamation text-danger me-2"></i>Data Leakage Incidents</h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($leakageIncidents as $incident)
                <div class="list-group-item d-flex align-items-start gap-3">
                    <span class="badge bg-danger mt-1">{{ strtoupper($incident->channel) }}</span>
                    <div class="min-w-0">
                        <div class="small fw-medium">{{ $incident->user->name ?? 'Unknown' }}</div>
                        <div class="text-muted small">{{ ucfirst(str_replace('_',' ',$incident->leakage_type)) }} detected</div>
                        <div class="text-muted" style="font-size:.7rem">{{ $incident->created_at->diffForHumans() }}</div>
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

@push('scripts')
<script>
const ctx = document.getElementById('weeklyChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($weeklyData['labels']) !!},
        datasets: [
            {
                label: 'Sessions',
                data: {!! json_encode($weeklyData['sessions']) !!},
                backgroundColor: 'rgba(79,70,229,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Alerts',
                data: {!! json_encode($weeklyData['alerts']) !!},
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($statusData['labels']) !!},
        datasets: [{
            data: {!! json_encode($statusData['values']) !!},
            backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#94a3b8'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        cutout: '65%',
    }
});
</script>
@endpush
