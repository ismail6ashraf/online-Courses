@extends('layouts.app')

@section('title', $assessment->title)
@section('page-title', 'Assessment')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-semibold mb-1">{{ $assessment->title }}</h4>
            <p class="text-muted small mb-0">
                {{ $assessment->course->title ?? $assessment->classSession?->course?->title ?? 'Course assessment' }}
            </p>
        </div>
        <a href="{{ route('student.courses.details', $assessment->course ?? $assessment->classSession->course) }}"
            class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($response)
        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-check-circle"></i>
            <div>You already submitted this assessment. You can update your answers below.</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border rounded-3">
        <div class="card-body p-4">
            @if($assessment->description)
                <div class="bg-light rounded-3 p-3 mb-4 text-muted small">
                    {{ $assessment->description }}
                </div>
            @endif

            <form action="{{ route('student.assessments.submit', $assessment) }}" method="POST">
                @csrf

                @foreach($assessment->fields as $field)
                    @php $savedValue = old('responses.' . $field->id, $response?->responses[$field->id] ?? null); @endphp
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            {{ $field->label }}
                            @if($field->required)
                                <span class="text-danger">*</span>
                            @endif
                        </label>

                        @switch($field->type)
                            @case('textarea')
                                <textarea name="responses[{{ $field->id }}]" rows="4"
                                    class="form-control rounded-3 @error('responses.' . $field->id) is-invalid @enderror"
                                    {{ $field->required ? 'required' : '' }}>{{ $savedValue }}</textarea>
                                @break

                            @case('rating')
                                <select name="responses[{{ $field->id }}]"
                                    class="form-select rounded-3 @error('responses.' . $field->id) is-invalid @enderror"
                                    {{ $field->required ? 'required' : '' }}>
                                    <option value="">Choose rating</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ (string) $savedValue === (string) $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @break

                            @case('select')
                                <select name="responses[{{ $field->id }}]"
                                    class="form-select rounded-3 @error('responses.' . $field->id) is-invalid @enderror"
                                    {{ $field->required ? 'required' : '' }}>
                                    <option value="">Choose</option>
                                    @foreach($field->options ?? [] as $option)
                                        <option value="{{ $option }}" {{ (string) $savedValue === (string) $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                                @break

                            @case('radio')
                                <div class="d-flex flex-column gap-2">
                                    @foreach($field->options ?? [] as $option)
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="responses[{{ $field->id }}]" value="{{ $option }}"
                                                {{ (string) $savedValue === (string) $option ? 'checked' : '' }}
                                                {{ $field->required ? 'required' : '' }}>
                                            <span class="form-check-label">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('checkbox')
                                @php $savedValues = is_array($savedValue) ? $savedValue : []; @endphp
                                <div class="d-flex flex-column gap-2">
                                    @foreach($field->options ?? [] as $option)
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="responses[{{ $field->id }}][]" value="{{ $option }}"
                                                {{ in_array($option, $savedValues, true) ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('number')
                                <input type="number" name="responses[{{ $field->id }}]" value="{{ $savedValue }}"
                                    class="form-control rounded-3 @error('responses.' . $field->id) is-invalid @enderror"
                                    {{ $field->required ? 'required' : '' }}>
                                @break

                            @default
                                <input type="text" name="responses[{{ $field->id }}]" value="{{ $savedValue }}"
                                    class="form-control rounded-3 @error('responses.' . $field->id) is-invalid @enderror"
                                    {{ $field->required ? 'required' : '' }}>
                        @endswitch

                        @error('responses.' . $field->id)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <a href="{{ route('student.courses.details', $assessment->course ?? $assessment->classSession->course) }}"
                        class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ $response ? 'Update Answers' : 'Submit Assessment' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
