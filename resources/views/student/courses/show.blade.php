@extends('layouts.app')

@section('title', $course->title)
@section('page-title', 'Course Details')

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h4>{{ $course->title }}</h4>
                    <p class="text-muted">{{ $course->description }}</p>
                    <div class="small text-muted">Instructor: {{ $course->instructor->name ?? 'N/A' }}</div>
                </div>

                @if(($enrollmentStatus ?? null) === 'completed')
                    <a href="{{ route('student.courses.certificate', $course) }}" class="btn btn-success">
                        <i class="bi bi-award me-1"></i> Certificate
                    </a>
                @endif
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
                        <div class="text-muted small">{{ $session->scheduled_at->format('d M Y H:i') }}</div>
                    </div>

                    @if($session->meeting_url)
                        <a href="{{ $session->meeting_url }}" target="_blank" class="btn btn-sm btn-primary">Join</a>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">No sessions yet</div>
            @endforelse
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Course Materials</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($course->materials as $material)
                        <div class="list-group-item">
                            <div class="fw-medium small">{{ $material->title }}</div>
                            <div class="text-muted small">{{ $material->description }}</div>
                            <div class="small mt-1">
                                @if($material->type === 'link')
                                    <a href="{{ $material->url }}" target="_blank" rel="noopener">Open link</a>
                                @elseif($material->file_path)
                                    <a href="{{ Storage::disk('public')->url($material->file_path) }}" target="_blank">
                                        {{ $material->file_name ?? 'Download file' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">No materials yet</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-semibold">Assignments</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($course->assignments as $assignment)
                        @php $submission = $assignment->submissionFor(auth()->user()); @endphp
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <div>
                                    <div class="fw-medium small">{{ $assignment->title }}</div>
                                    <div class="text-muted small">{{ $assignment->instructions }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-muted border">{{ $assignment->points }} pts</span>
                                    <div class="text-muted mt-1" style="font-size:.72rem">
                                        {{ $assignment->due_at?->format('d M Y H:i') ?? 'No due date' }}
                                    </div>
                                </div>
                            </div>

                            @if($submission)
                                <div class="alert alert-success py-2 small mb-3">
                                    Submitted {{ $submission->submitted_at?->diffForHumans() }}.
                                    @if($submission->grade !== null)
                                        <span class="fw-semibold ms-1">Grade: {{ $submission->grade }} / {{ $assignment->points }}</span>
                                    @endif
                                    @if($submission->file_path)
                                        <a href="{{ Storage::disk('public')->url($submission->file_path) }}" target="_blank" class="alert-link">
                                            View file
                                        </a>
                                    @endif
                                    @if($submission->feedback)
                                        <div class="mt-1">Feedback: {{ $submission->feedback }}</div>
                                    @endif
                                </div>
                            @endif

                            <form action="{{ route('student.courses.assignments.submit', [$course, $assignment]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="answer" rows="3" class="form-control" placeholder="Write your answer...">{{ old('answer', $submission?->answer) }}</textarea>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="file" name="file" class="form-control form-control-sm">
                                    <button class="btn btn-sm btn-primary flex-shrink-0">
                                        {{ $submission ? 'Update' : 'Submit' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">No assignments yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
