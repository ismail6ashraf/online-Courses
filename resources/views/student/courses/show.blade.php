@extends('layouts.app')

@section('title', $course->title)
@section('page-title', 'Course Details')

@section('content')

    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ $course->title }}</h4>
            <p class="text-muted">{{ $course->description }}</p>

            <div class="small text-muted">
                Instructor: {{ $course->instructor->name ?? '—' }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-transparent border-0 pt-3">
            <h6 class="mb-0 fw-semibold">Course Sessions</h6>
        </div>

        <div class="list-group list-group-flush">
            @forelse($course->sessions as $session)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-medium">{{ $session->title }}</div>
                        <div class="text-muted small">
                            {{ $session->scheduled_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    @if($session->meeting_url)
                        <a href="{{ $session->meeting_url }}" target="_blank" class="btn btn-sm btn-primary">
                            Join
                        </a>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    No sessions yet
                </div>
            @endforelse
        </div>
    </div>

@endsection
