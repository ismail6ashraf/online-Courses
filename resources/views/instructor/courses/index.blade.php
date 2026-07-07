@extends('layouts.app')

@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">My Courses</h4>
            <p class="text-muted small mb-0">{{ $courses->count() }} course(s) total</p>
        </div>
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> New Course
        </a>
    </div>

    @forelse($courses as $course)
        <div class="card border rounded-3 mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">

                    {{-- الأيقونة + المعلومات --}}
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:46px; height:46px; font-size:20px;">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">{{ $course->title }}</h6>
                            <p class="text-muted small mb-2">{{ $course->description }}</p>

                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                @if($course->level)
                                    <span class="badge bg-light text-muted border small">
                                        <i class="bi bi-bar-chart me-1"></i>{{ $course->level }}
                                    </span>
                                @endif

                                @if($course->language)
                                    <span class="badge bg-light text-muted border small">
                                        <i class="bi bi-translate me-1"></i>{{ $course->language }}
                                    </span>
                                @endif

                                @if($course->duration_hours)
                                    <span class="badge bg-light text-muted border small">
                                        <i class="bi bi-clock me-1"></i>{{ $course->duration_hours }}h
                                    </span>
                                @endif

                                <span
                                    class="badge rounded-pill small
                                    {{ $course->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- الأزرار --}}
                    <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                        <a href="{{ route('instructor.courses.show', $course->id) }}"
                            class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                            <i class="bi bi-eye"></i> Manage
                        </a>
                        <a href="{{ route('instructor.courses.edit', $course->id) }}"
                            class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('instructor.courses.destroy', $course->id) }}" method="POST"
                            onsubmit="return confirm('Delete this course?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @empty
        <div class="card border rounded-3">
            <div class="card-body text-center py-5">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                    style="width:56px; height:56px; font-size:24px;">
                    <i class="bi bi-journal-x text-muted"></i>
                </div>
                <h6 class="fw-semibold text-muted mb-1">No courses yet</h6>
                <p class="text-muted small mb-3">Start by creating your first course</p>
                <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Course
                </a>
            </div>
        </div>
    @endforelse

@endsection
