@extends('layouts.app')

@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Courses</h4>

    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
        + New Course
    </a>
</div>

<div class="card">
    <div class="card-body">
        @forelse($courses as $course)
            <div class="border-bottom py-2">
                <strong>{{ $course->title }}</strong>
                <p class="text-muted mb-0">{{ $course->description }}</p>
            </div>
        @empty
            <p class="text-muted mb-0">No courses found</p>
        @endforelse
    </div>
</div>

@endsection
