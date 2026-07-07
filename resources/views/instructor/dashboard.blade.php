@extends('layouts.app')

@section('title', 'Instructor Dashboard')
@section('page-title', 'My Dashboard')

@section('content')

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-3">
                        <i class="bi bi-credit-card"></i>
                        Billing & Subscription
                    </h5>

                    @if($subscription && $subscription->isActive())
                        <p class="mb-1">
                            <strong>Current Plan:</strong> {{ $plan->name }}
                        </p>

                        <p class="mb-1">
                            <strong>Courses Used:</strong>
                            {{ $coursesCount }} / {{ $plan->max_courses ?? 'Unlimited' }}
                            @if($plan->max_courses)
                                @php
                                    $coursePercentage = min(100, ($coursesCount / $plan->max_courses) * 100);
                                @endphp

                                <div class="progress mt-2 mb-2" style="height: 8px;">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        style="width: {{ $coursePercentage }}%">
                                    </div>
                                </div>
                            @endif
                        </p>

                        <p class="mb-1">
                            <strong>Students Limit:</strong>
                            {{ $plan->max_students ?? 'Unlimited' }}
                        </p>

                        <p class="mb-0 text-muted">
                            <strong>Expires:</strong>
                            {{ $subscription->end_date->format('Y-m-d') }}
                            <br>
                            <strong>Days Remaining:</strong>
                            {{ now()->diffInDays($subscription->end_date, false) }} days
                        </p>
                    @else
                        <div class="alert alert-warning mb-0">
                            You don't have an active subscription.
                        </div>
                    @endif
                </div>

                <a href="{{ route('instructor.pricing') }}" class="btn btn-primary">
                    {{ $subscription && $subscription->isActive() ? 'Upgrade Plan' : 'Choose Plan' }}
                </a>
            </div>
        </div>
    </div>


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

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Total Students</div>
                        <div class="fs-4 fw-bold">{{ $stats['total_students'] }}</div>
                    </div>
                    <i class="bi bi-people text-primary fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Materials</div>
                        <div class="fs-4 fw-bold">{{ $stats['course_materials'] }}</div>
                    </div>
                    <i class="bi bi-folder2-open text-info fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Assignments</div>
                        <div class="fs-4 fw-bold">{{ $stats['published_assignments'] }}</div>
                    </div>
                    <i class="bi bi-list-check text-warning fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Submissions</div>
                        <div class="fs-4 fw-bold">{{ $stats['assignment_submissions'] }}</div>
                    </div>
                    <i class="bi bi-inbox text-success fs-3"></i>
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
                                    <a href="{{ route('instructor.sessions.show', $session) }}"
                                        class="btn btn-xs btn-sm btn-outline-secondary">View</a>
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
                                        <span
                                            class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning text-dark' : 'secondary') }} small">
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
                                        <div class="{{ $task->due_at->isPast() ? 'text-danger' : 'text-muted' }}"
                                            style="font-size:.72rem">
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

    <div class="row g-3 mt-1">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Session Mix</h6>
                </div>
                <div class="card-body">
                    <canvas id="instructorSessionMix" height="220"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Recent Assignment Submissions</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentSubmissions as $submission)
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-medium small">{{ $submission->assignment->title ?? 'Assignment' }}</div>
                                <div class="text-muted small">
                                    {{ $submission->student->name ?? 'Unknown student' }}
                                    @if($submission->assignment?->course)
                                        · {{ $submission->assignment->course->title }}
                                    @endif
                                </div>
                            </div>
                            <span class="text-muted small flex-shrink-0">{{ $submission->submitted_at?->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted small py-4">No submissions yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const instructorSessionMix = document.getElementById('instructorSessionMix');
new Chart(instructorSessionMix, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($sessionMix['labels']) !!},
        datasets: [{
            data: {!! json_encode($sessionMix['values']) !!},
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
