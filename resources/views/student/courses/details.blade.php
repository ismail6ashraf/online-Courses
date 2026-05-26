@extends('layouts.app')
@section('title', $course->title)
@section('page-title', 'Course Details')

@section('content')
    <div class="container py-4">

        {{-- Back --}}
        <a href="{{ route('student.dashboard') }}"
            class="text-muted text-decoration-none d-inline-flex align-items-center gap-1 mb-4 small">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        {{-- بطاقة الكورس الرئيسية --}}
        <div class="card border rounded-3 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width:52px; height:52px; font-size:22px;">
                        <i class="bi bi-book"></i>
                    </div>
                    <div>
                        <h1 class="h5 fw-semibold mb-1">{{ $course->title }}</h1>
                        <p class="text-muted small mb-0">{{ $course->description }}</p>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill px-3">
                    {{ $course->status }}
                </span>
            </div>

            <div class="row g-3 border-top pt-4">
                <div class="col-4">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <p class="text-muted small mb-1">Level</p>
                        <p class="fw-medium mb-0">{{ $course->level ?: '—' }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <p class="text-muted small mb-1">Language</p>
                        <p class="fw-medium mb-0">{{ $course->language }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <p class="text-muted small mb-1">Duration</p>
                        <p class="fw-medium mb-0">{{ $course->duration_hours }} hours</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions + Assessments --}}
        <div class="row g-4">

            {{-- Sessions --}}
            <div class="col-md-6">
                <div class="card border rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h6 fw-semibold mb-0">
                            <i class="bi bi-calendar3 me-2 text-primary"></i>Sessions
                        </h2>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">
                            {{ $sessions->count() }}
                        </span>
                    </div>

                    @forelse($sessions as $session)
                        <div class="d-flex align-items-center gap-3 bg-light rounded-3 p-3 mb-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:32px; height:32px; font-size:13px; font-weight:500;">
                                {{ $loop->iteration }}
                            </div>
                            <div>
                                <p class="fw-medium small mb-0">{{ $session->title }}</p>
                                <p class="text-muted mb-0" style="font-size:11px;">
                                    {{ $session->scheduled_at ?? '' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No sessions yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Assessments --}}
            <div class="col-md-6">
                <div class="card border rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h6 fw-semibold mb-0">
                            <i class="bi bi-clipboard-check me-2 text-purple"></i>Assessments
                        </h2>
                        <span class="badge rounded-pill" style="background:#EEEDFE; color:#534AB7;">
                            {{ $assessments->count() }}
                        </span>
                    </div>

                    @forelse($assessments as $assessment)
                        <div class="d-flex align-items-center gap-3 bg-light rounded-3 p-3 mb-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:32px; height:32px; background:#EEEDFE;">
                                <i class="bi bi-file-text" style="color:#534AB7; font-size:13px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-medium small mb-0">{{ $assessment->title }}</p>
                                <p class="text-muted mb-0" style="font-size:11px;">
                                    {{ ucfirst(str_replace('_', ' ', $assessment->type)) }}
                                </p>
                            </div>
                            @if($isEnrolled)
                                <a href="{{ route('student.assessments.show', $assessment) }}"
                                    class="btn btn-sm btn-primary">
                                    Choose
                                </a>
                            @else
                                <form action="{{ route('student.courses.enroll', $course) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Enroll first</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No assessments yet</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
