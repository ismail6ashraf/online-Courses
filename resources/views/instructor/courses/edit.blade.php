@extends('layouts.app')

@section('title', 'Edit Course')
@section('page-title', 'Edit Course')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Edit Course</h4>
            <p class="text-muted small mb-0">Update your course information</p>
        </div>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('instructor.courses.update', $course->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- العمود الرئيسي --}}
            <div class="col-md-8">

                {{-- المعلومات الأساسية --}}
                <div class="card border rounded-3 p-4 mb-4">
                    <h6 class="fw-semibold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary"></i> Basic Information
                    </h6>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3 @error('title') is-invalid @enderror"
                            value="{{ old('title', $course->title) }}" placeholder="e.g. Introduction to JavaScript">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-medium">Description</label>
                        <textarea name="description"
                            class="form-control rounded-3 @error('description') is-invalid @enderror" rows="4"
                            placeholder="Describe what students will learn...">{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- تفاصيل الكورس --}}
                <div class="card border rounded-3 p-4">
                    <h6 class="fw-semibold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-primary"></i> Course Details
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Level</label>
                            <select name="level" class="form-select rounded-3 @error('level') is-invalid @enderror">
                                <option value="">— Select level —</option>
                                @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                                    <option value="{{ $level }}" {{ old('level', $course->level) === $level ? 'selected' : '' }}>
                                        {{ $level }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Language</label>
                            <select name="language" class="form-select rounded-3 @error('language') is-invalid @enderror">
                                <option value="">— Select language —</option>
                                @foreach(['Arabic', 'English', 'French'] as $lang)
                                    <option value="{{ $lang }}" {{ old('language', $course->language) === $lang ? 'selected' : '' }}>
                                        {{ $lang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Duration (hours)</label>
                            <input type="number" name="duration_hours"
                                class="form-control rounded-3 @error('duration_hours') is-invalid @enderror"
                                value="{{ old('duration_hours', $course->duration_hours) }}" min="1" placeholder="e.g. 12">
                            @error('duration_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Status</label>
                            <select name="status" class="form-select rounded-3 @error('status') is-invalid @enderror">
                                @foreach(['active', 'inactive', 'archived'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $course->status) === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- العمود الجانبي --}}
            <div class="col-md-4">

                {{-- معلومات الكورس --}}
                <div class="card border rounded-3 p-4 mb-4">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart text-primary"></i> Course Stats
                    </h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Students</span>
                            <span class="fw-medium">{{ $course->students->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Sessions</span>
                            <span class="fw-medium">{{ $course->sessions->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Assessments</span>
                            <span class="fw-medium">{{ $course->assessments->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small">Created</span>
                            <span class="fw-medium small">{{ $course->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="card border rounded-3 p-4">
                    <button type="submit" class="btn btn-primary w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="{{ route('instructor.courses.index') }}"
                        class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>

@endsection
