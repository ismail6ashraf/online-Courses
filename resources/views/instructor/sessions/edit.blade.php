@extends('layouts.app')

@section('title', 'Edit Session')
@section('page-title', 'Edit Session')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('instructor.sessions.update', $session) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Session Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $session->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $session->description) }}</textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Scheduled At</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control"
                                   value="{{ old('scheduled_at', $session->scheduled_at->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Platform</label>
                            <input type="text" name="platform" class="form-control" value="{{ old('platform', $session->platform) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Meeting URL</label>
                        <input type="url" name="meeting_url" class="form-control" value="{{ old('meeting_url', $session->meeting_url) }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('instructor.sessions.show', $session) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
