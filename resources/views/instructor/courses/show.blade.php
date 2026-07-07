@extends('layouts.app')

@section('title', $course->title)
@section('page-title', 'Course Workspace')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-semibold mb-1">{{ $course->title }}</h4>
            <p class="text-muted small mb-0">{{ $course->description ?: 'Manage materials, assignments, sessions, and students.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('instructor.sessions.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-plus me-1"></i> New Session
            </a>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card p-3 h-100"><div class="small text-muted mb-1">Students</div><div class="fs-3 fw-bold text-primary">{{ $course->students->count() }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card p-3 h-100"><div class="small text-muted mb-1">Sessions</div><div class="fs-3 fw-bold text-success">{{ $course->sessions->count() }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card p-3 h-100"><div class="small text-muted mb-1">Materials</div><div class="fs-3 fw-bold text-info">{{ $course->materials->count() }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card p-3 h-100"><div class="small text-muted mb-1">Assignments</div><div class="fs-3 fw-bold text-warning">{{ $course->assignments->count() }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-4 mb-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Add Material</h6>
                <form action="{{ route('instructor.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Title</label>
                        <input name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Description</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Type</label>
                        <select name="type" class="form-select" id="materialType">
                            <option value="file">File</option>
                            <option value="link">Link</option>
                        </select>
                    </div>
                    <div class="mb-3" id="materialFileWrap">
                        <label class="form-label small fw-medium">File</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="mb-3 d-none" id="materialUrlWrap">
                        <label class="form-label small fw-medium">URL</label>
                        <input type="url" name="url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" checked>
                        <label class="form-check-label small">Published to students</label>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add Material</button>
                </form>
            </div>

            <div class="card p-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-clipboard-plus text-primary me-2"></i>Create Assignment</h6>
                <form action="{{ route('instructor.courses.assignments.store', $course) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Title</label>
                        <input name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Instructions</label>
                        <textarea name="instructions" rows="4" class="form-control"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-medium">Due date</label>
                            <input type="datetime-local" name="due_at" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-medium">Points</label>
                            <input type="number" name="points" value="100" min="1" max="1000" class="form-control">
                        </div>
                    </div>
                    <div class="form-check form-switch my-3">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" checked>
                        <label class="form-check-label small">Published to students</label>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Create Assignment</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-semibold mb-0">Course Materials</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($course->materials as $material)
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-medium small">{{ $material->title }}</div>
                                <div class="text-muted small">{{ $material->description }}</div>
                                <div class="small mt-1">
                                    @if($material->type === 'link')
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener">Open link</a>
                                    @elseif($material->file_path)
                                        <a href="{{ Storage::disk('public')->url($material->file_path) }}" target="_blank">{{ $material->file_name }}</a>
                                    @endif
                                    <span class="badge {{ $material->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} ms-2">
                                        {{ $material->is_published ? 'Published' : 'Hidden' }}
                                    </span>
                                </div>
                            </div>
                            <form action="{{ route('instructor.courses.materials.destroy', [$course, $material]) }}" method="POST"
                                onsubmit="return confirm('Remove this material?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted small py-4">No materials yet</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-semibold mb-0">Assignments & Submissions</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($course->assignments as $assignment)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-medium small">{{ $assignment->title }}</div>
                                    <div class="text-muted small">{{ $assignment->instructions }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="badge bg-light text-muted border">{{ $assignment->points }} pts</span>
                                        <span class="badge bg-light text-muted border">Due {{ $assignment->due_at?->format('d M Y H:i') ?? 'Anytime' }}</span>
                                        <span class="badge {{ $assignment->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $assignment->is_published ? 'Published' : 'Hidden' }}</span>
                                        <span class="badge bg-primary-subtle text-primary">{{ $assignment->submissions->count() }} submissions</span>
                                    </div>
                                </div>
                                <form action="{{ route('instructor.courses.assignments.destroy', [$course, $assignment]) }}" method="POST"
                                    onsubmit="return confirm('Remove this assignment and its submissions?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>

                            @if($assignment->submissions->isNotEmpty())
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Submitted</th>
                                                <th>File</th>
                                                <th style="width:260px">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($assignment->submissions as $submission)
                                                <tr>
                                                    <td>{{ $submission->student->name ?? 'Unknown' }}</td>
                                                    <td>{{ $submission->submitted_at?->format('d M Y H:i') }}</td>
                                                    <td>
                                                        @if($submission->file_path)
                                                            <a href="{{ Storage::disk('public')->url($submission->file_path) }}" target="_blank">{{ $submission->file_name }}</a>
                                                        @else
                                                            <span class="text-muted">Text answer</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <form action="{{ route('instructor.courses.assignments.submissions.grade', [$course, $assignment, $submission]) }}" method="POST" class="d-flex gap-2 align-items-start">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div>
                                                                <input type="number" name="grade" value="{{ $submission->grade }}" min="0" max="{{ $assignment->points }}"
                                                                    class="form-control form-control-sm" placeholder="0-{{ $assignment->points }}">
                                                                <input type="text" name="feedback" value="{{ $submission->feedback }}"
                                                                    class="form-control form-control-sm mt-1" placeholder="Feedback">
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-primary">Save</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted small py-4">No assignments yet</div>
                    @endforelse
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-semibold mb-0">Gradebook & Completion</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Assignment Average</th>
                                <th>Completed Work</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($course->students as $student)
                                @php
                                    $submissions = $course->assignments
                                        ->flatMap->submissions
                                        ->where('student_id', $student->id);
                                    $graded = $submissions->whereNotNull('grade');
                                    $pointsEarned = $graded->sum('grade');
                                    $pointsPossible = $graded->sum(fn ($submission) => $submission->assignment?->points ?? 0);
                                    $average = $pointsPossible > 0 ? round(($pointsEarned / $pointsPossible) * 100) : null;
                                @endphp
                                <tr>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $average !== null ? $average . '%' : 'Not graded' }}</td>
                                    <td>{{ $submissions->count() }} / {{ $course->assignments->count() }}</td>
                                    <td>
                                        <span class="badge {{ $student->pivot->status === 'completed' ? 'bg-success-subtle text-success' : 'bg-light text-muted border' }}">
                                            {{ ucfirst($student->pivot->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($student->pivot->status !== 'completed')
                                            <form action="{{ route('instructor.courses.students.complete', [$course, $student]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-award me-1"></i> Mark Complete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-success small">Certificate unlocked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No enrolled students yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const materialType = document.getElementById('materialType');
const materialFileWrap = document.getElementById('materialFileWrap');
const materialUrlWrap = document.getElementById('materialUrlWrap');

if (materialType) {
    materialType.addEventListener('change', () => {
        const isLink = materialType.value === 'link';
        materialFileWrap.classList.toggle('d-none', isLink);
        materialUrlWrap.classList.toggle('d-none', !isLink);
    });
}
</script>
@endpush
