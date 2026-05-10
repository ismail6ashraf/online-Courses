@extends('layouts.app')

@section('title', 'Create Assessment')
@section('page-title', 'Create Assessment Form')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('instructor.assessments.store') }}" method="POST" id="assessmentForm">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium">Assessment Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="pre_session">Pre-Session</option>
                                <option value="mid_session">Mid-Session</option>
                                <option value="post_session">Post-Session</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Assessment Fields</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addField()">
                            <i class="bi bi-plus-lg me-1"></i>Add Field
                        </button>
                    </div>

                    <div id="fieldsContainer"></div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Create Assessment</button>
                        <a href="{{ route('instructor.assessments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let fieldCount = 0;

function addField() {
    const container = document.getElementById('fieldsContainer');
    const idx = fieldCount++;
    const div = document.createElement('div');
    div.className = 'card p-3 mb-3 field-item';
    div.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <strong class="small">Field #${idx + 1}</strong>
            <button type="button" class="btn btn-xs btn-sm btn-outline-danger" onclick="this.closest('.field-item').remove()">Remove</button>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="fields[${idx}][label]" class="form-control form-control-sm" placeholder="Field label *" required>
            </div>
            <div class="col-md-4">
                <select name="fields[${idx}][type]" class="form-select form-select-sm" onchange="toggleOptions(this, ${idx})">
                    <option value="text">Short Text</option>
                    <option value="textarea">Long Text</option>
                    <option value="rating">Rating (1-5)</option>
                    <option value="select">Dropdown</option>
                    <option value="radio">Multiple Choice</option>
                    <option value="number">Number</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="fields[${idx}][required]" value="1" id="req_${idx}">
                    <label class="form-check-label small" for="req_${idx}">Required</label>
                </div>
            </div>
            <div class="col-12 options-container-${idx} d-none">
                <input type="text" name="fields[${idx}][options][]" class="form-control form-control-sm" placeholder="Option 1">
                <button type="button" class="btn btn-xs btn-sm btn-link mt-1" onclick="addOption(${idx})">+ Add Option</button>
            </div>
        </div>
    `;
    container.appendChild(div);
}

function toggleOptions(select, idx) {
    const container = document.querySelector(`.options-container-${idx}`);
    if (['select','radio','checkbox'].includes(select.value)) {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }
}

function addOption(idx) {
    const container = document.querySelector(`.options-container-${idx}`);
    const input = document.createElement('input');
    input.type = 'text';
    input.name = `fields[${idx}][options][]`;
    input.className = 'form-control form-control-sm mt-1';
    input.placeholder = 'Option';
    container.insertBefore(input, container.querySelector('button'));
}

// Add first field by default
addField();
</script>
@endpush
