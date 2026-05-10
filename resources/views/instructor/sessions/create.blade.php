@extends('layouts.app')

@section('title', 'Create Session')
@section('page-title', 'Create New Session')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('instructor.sessions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select course...</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Session Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Scheduled At</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Platform</label>
                            <input type="text" name="platform" class="form-control" value="{{ old('platform') }}" placeholder="Zoom, Teams, etc.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Meeting URL</label>
                        <input type="url" name="meeting_url" class="form-control" value="{{ old('meeting_url') }}" placeholder="https://...">
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="recording_enabled" value="1" id="recording">
                        <label class="form-check-label" for="recording">Enable Recording</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Session</button>
                        <a href="{{ route('instructor.sessions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
