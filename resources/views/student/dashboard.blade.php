@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'My Learning')

@section('content')


    <div class="row g-3 mb-4">

        {{-- Enrolled Courses --}}
        <div class="col-sm-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Enrolled Courses</div>
                        <div class="fs-3 fw-bold">{{ $stats['enrolled_courses'] }}</div>
                    </div>
                    <i class="bi bi-book fs-2 opacity-50"></i>
                </div>
            </div>
        </div>

        {{-- Upcoming Sessions --}}
        <div class="col-sm-4">
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

        {{-- Attendance Rate --}}
        <div class="col-sm-4">
            <div class="stat-card" style="background:linear-gradient(135deg,#059669,#10b981)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 mb-1">Attendance Rate</div>
                        <div class="fs-3 fw-bold">{{ $stats['attendance_rate'] }}%</div>
                    </div>
                    <i class="bi bi-check-circle fs-2 opacity-50"></i>
                </div>
            </div>
        </div>

    </div>


    <div class="card mb-4">
        <div class="card-header bg-transparent border-0 pt-3">
            <h6 class="mb-0 fw-semibold">Available Courses</h6>
        </div>

        <div class="list-group list-group-flush">
            @forelse($availableCourses as $course)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium small">{{ $course->title }}</div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $course->description }}
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-primary">Enroll</button>
                        </form>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('student.courses.details', $course->id) }}">
                            View Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    No courses available
                </div>
            @endforelse
        </div>
    </div>


    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Upcoming Sessions</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($upcomingSessions as $session)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-medium small">{{ $session->title }}</div>
                                    <div class="text-muted" style="font-size:.75rem">
                                        <i class="bi bi-person me-1"></i>{{ $session->instructor->name ?? '—' }}
                                        | <i class="bi bi-book me-1"></i>{{ $session->course->title ?? '—' }}
                                    </div>
                                    <div class="text-primary" style="font-size:.75rem">
                                        <i class="bi bi-clock me-1"></i>{{ $session->scheduled_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2">

                                    <a href="{{ route('student.courses.show', $session->course_id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>

                                    @if($session->meeting_url)
                                        <a href="{{ $session->meeting_url }}" target="_blank" class="btn btn-sm btn-primary">
                                            Join
                                        </a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">No upcoming sessions</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Attendance History</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentAttendances as $att)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-medium">{{ $att->session->title ?? '—' }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $att->created_at->format('d M Y') }}</div>
                            </div>
                            <span
                                class="badge bg-{{ $att->status === 'present' ? 'success' : ($att->status === 'absent' ? 'danger' : 'warning text-dark') }}">
                                {{ ucfirst($att->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">No attendance records</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection
