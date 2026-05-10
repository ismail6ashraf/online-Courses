@extends('layouts.app')

@section('title', $assessment->title)
@section('page-title', 'Assessment Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5>{{ $assessment->title }}</h5>
        <span class="badge bg-secondary me-1">{{ ucfirst(str_replace('_',' ',$assessment->type)) }}</span>
        <span class="badge bg-{{ $assessment->status==='active'?'success':'warning text-dark' }}">{{ ucfirst($assessment->status) }}</span>
    </div>
    <a href="{{ route('instructor.assessments.index') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0">Form Fields</h6></div>
            <div class="list-group list-group-flush">
                @foreach($assessment->fields as $field)
                <div class="list-group-item">
                    <div class="fw-medium small">{{ $field->label }} @if($field->required)<span class="text-danger">*</span>@endif</div>
                    <small class="text-muted">{{ ucfirst($field->type) }}</small>
                    @if($field->options)
                    <div class="text-muted small">Options: {{ implode(', ', $field->options) }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-transparent"><h6 class="mb-0">Submit Response</h6></div>
            <div class="card-body">
                <form action="{{ route('instructor.assessments.respond', $assessment) }}" method="POST">
                    @csrf
                    @foreach($assessment->fields as $field)
                    <div class="mb-3">
                        <label class="form-label small fw-medium">{{ $field->label }}</label>
                        @switch($field->type)
                            @case('textarea')
                                <textarea name="responses[{{ $field->id }}]" class="form-control form-control-sm" rows="2" {{ $field->required?'required':'' }}></textarea>
                                @break
                            @case('rating')
                                <select name="responses[{{ $field->id }}]" class="form-select form-select-sm" {{ $field->required?'required':'' }}>
                                    @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                                </select>
                                @break
                            @case('select')
                                <select name="responses[{{ $field->id }}]" class="form-select form-select-sm" {{ $field->required?'required':'' }}>
                                    @foreach($field->options ?? [] as $opt)<option>{{ $opt }}</option>@endforeach
                                </select>
                                @break
                            @default
                                <input type="{{ $field->type === 'number' ? 'number' : 'text' }}" name="responses[{{ $field->id }}]"
                                       class="form-control form-control-sm" {{ $field->required?'required':'' }}>
                        @endswitch
                    </div>
                    @endforeach
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Recommendations / Notes</label>
                        <textarea name="recommendations" class="form-control form-control-sm" rows="3"
                                  placeholder="Enter recommendations (will auto-generate tasks)..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit & Generate Tasks</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0">Responses ({{ $assessment->responses->count() }})</h6></div>
            <div class="list-group list-group-flush">
                @forelse($assessment->responses as $response)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small fw-medium">{{ $response->student->name ?? 'General' }}</div>
                            @if($response->classSession)
                            <div class="text-muted" style="font-size:.72rem">Session: {{ $response->classSession->title }}</div>
                            @endif
                            @if($response->recommendations)
                            <div class="mt-1 p-2 bg-light rounded small">{{ Str::limit($response->recommendations, 100) }}</div>
                            @endif
                        </div>
                        <div>
                            <small class="text-muted">{{ $response->created_at->format('d M Y') }}</small>
                            @if($response->tasks_generated)
                            <span class="badge bg-success d-block mt-1">Tasks Generated</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">No responses yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
