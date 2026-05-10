@extends('layouts.app')

@section('title', 'Live: ' . $session->title)
@section('page-title', '🔴 LIVE: ' . $session->title)

@section('content')

<div class="row g-3">
    <div class="col-lg-8">
        {{-- Video Area --}}
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="ratio ratio-16x9 bg-dark rounded" id="videoArea">
                    <div class="d-flex flex-column align-items-center justify-content-center text-white">
                        <i class="bi bi-camera-video-fill fs-1 mb-2 text-danger"></i>
                        <p class="mb-1">Session is Live</p>
                        @if($session->meeting_url)
                        <a href="{{ $session->meeting_url }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open in {{ $session->platform ?? 'Meeting Room' }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Session Controls --}}
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger animate-pulse">LIVE</span>
                    <span class="text-muted small" id="sessionTimer">00:00:00</span>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="toggleMic">
                        <i class="bi bi-mic-fill"></i> Mic On
                    </button>
                    <form action="{{ route('instructor.sessions.end', $session) }}" method="POST"
                          onsubmit="return confirm('End this session?')">
                        @csrf
                        <button class="btn btn-sm btn-danger">
                            <i class="bi bi-stop-fill me-1"></i>End Session
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Live Stats --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0 fw-semibold">Live Monitoring</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-primary" id="attendeeCount">—</div>
                            <small class="text-muted">Attendees</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-warning" id="deadAirSeconds">{{ $session->dead_air_seconds }}s</div>
                            <small class="text-muted">Dead Air</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-danger" id="behaviorAlerts">—</div>
                            <small class="text-muted">Behavior Alerts</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-info" id="chatMessages">—</div>
                            <small class="text-muted">Chat Messages</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Speech Analysis Status --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cpu me-2 text-primary"></i>AI Monitoring</h6>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <small><i class="bi bi-mic text-success me-2"></i>Speech Analysis</small>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <small><i class="bi bi-shield-check text-primary me-2"></i>Data Leakage Prevention</small>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <small><i class="bi bi-soundwave text-warning me-2"></i>Dead Air Detection</small>
                    <span class="badge bg-success" id="deadAirStatus">Monitoring</span>
                </div>
            </div>
        </div>

        {{-- Tasks for this session --}}
        @if($session->instructorTasks->isNotEmpty())
        <div class="card">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-list-task text-warning me-2"></i>Session Tasks</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($session->instructorTasks->take(5) as $task)
                <div class="list-group-item d-flex gap-2 align-items-start">
                    <input type="checkbox" class="form-check-input mt-1" {{ $task->status === 'completed' ? 'checked' : '' }}>
                    <div class="small">{{ $task->title }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const sessionId = {{ $session->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentSecond = 0;
let startTime = Date.now();
let silenceTimer = null;
let isSilent = false;

// Session timer
setInterval(() => {
    currentSecond++;
    const elapsed = Date.now() - startTime;
    const h = String(Math.floor(elapsed / 3600000)).padStart(2,'0');
    const m = String(Math.floor((elapsed % 3600000) / 60000)).padStart(2,'0');
    const s = String(Math.floor((elapsed % 60000) / 1000)).padStart(2,'0');
    document.getElementById('sessionTimer').textContent = `${h}:${m}:${s}`;
}, 1000);

// Heartbeat every 5 seconds
setInterval(() => {
    fetch(`/api/sessions/${sessionId}/heartbeat`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({current_second: currentSecond}),
    });
}, 5000);

// Fetch live stats every 10 seconds
async function fetchStats() {
    const resp = await fetch(`/api/sessions/${sessionId}/stats`, {
        headers: {'Accept': 'application/json'}
    });
    if (resp.ok) {
        const data = await resp.json();
        document.getElementById('attendeeCount').textContent  = data.attendees ?? '—';
        document.getElementById('deadAirSeconds').textContent = (data.dead_air?.total_seconds ?? 0) + 's';
        document.getElementById('behaviorAlerts').textContent = data.behavior_alerts ?? '—';
        document.getElementById('chatMessages').textContent   = data.chat_messages ?? '—';
    }
}
setInterval(fetchStats, 10000);
fetchStats();
</script>
@endpush
