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

        <div class="row g-4 mt-1">
            <div class="col-md-6">
                <div class="card border rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h6 fw-semibold mb-0">
                            <i class="bi bi-folder2-open me-2 text-primary"></i>Materials
                        </h2>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $materials->count() }}</span>
                    </div>

                    @forelse($materials as $material)
                        <div class="bg-light rounded-3 p-3 mb-2">
                            <div class="fw-medium small">{{ $material->title }}</div>
                            <div class="text-muted small">{{ $material->description }}</div>
                            @if($isEnrolled)
                                <div class="mt-2 small">
                                    @if($material->type === 'link')
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener">Open link</a>
                                    @elseif($material->file_path)
                                        <a href="{{ Storage::disk('public')->url($material->file_path) }}" target="_blank">
                                            {{ $material->file_name ?? 'Download file' }}
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary mt-2">Enroll to access</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No materials yet</p>
                    @endforelse
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="h6 fw-semibold mb-0">
                            <i class="bi bi-list-check me-2 text-primary"></i>Assignments
                        </h2>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $assignments->count() }}</span>
                    </div>

                    @forelse($assignments as $assignment)
                        @php $submission = $assignment->submissionFor(auth()->user()); @endphp
                        <div class="bg-light rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-medium small">{{ $assignment->title }}</div>
                                    <div class="text-muted small">{{ $assignment->instructions }}</div>
                                </div>
                                <span class="badge bg-light text-muted border align-self-start">{{ $assignment->points }} pts</span>
                            </div>
                            <div class="text-muted mt-2" style="font-size:11px">
                                Due {{ $assignment->due_at?->format('d M Y H:i') ?? 'Anytime' }}
                            </div>
                            @if($submission)
                                <span class="badge bg-success-subtle text-success mt-2">Submitted {{ $submission->submitted_at?->diffForHumans() }}</span>
                            @elseif($isEnrolled)
                                <span class="badge bg-warning-subtle text-warning mt-2">Pending</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary mt-2">Enroll to submit</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">No assignments yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
