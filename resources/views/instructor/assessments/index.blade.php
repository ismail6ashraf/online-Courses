@extends('layouts.app')

@section('title', 'Assessments')
@section('page-title', 'My Assessments')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('instructor.assessments.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Assessment
    </a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>Type</th><th>Status</th><th>Responses</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($assessments as $assessment)
                <tr>
                    <td class="fw-medium">{{ $assessment->title }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$assessment->type)) }}</span></td>
                    <td><span class="badge bg-{{ $assessment->status==='active'?'success':'warning text-dark' }}">{{ ucfirst($assessment->status) }}</span></td>
                    <td class="text-muted">{{ $assessment->responses_count }}</td>
                    <td class="text-muted small">{{ $assessment->created_at->format('d M Y') }}</td>
                    <td><a href="{{ route('instructor.assessments.show', $assessment) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No assessments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $assessments->links() }}</div>
</div>
@endsection
