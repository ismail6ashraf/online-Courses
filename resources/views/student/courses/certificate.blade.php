@extends('layouts.app')

@section('title', 'Certificate')
@section('page-title', 'Certificate')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('student.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-success">
            <i class="bi bi-printer me-1"></i> Save as PDF
        </button>
    </div>

    <div class="certificate-page bg-white border rounded-3 p-5 text-center">
        <div class="border border-2 rounded-3 p-5">
            <div class="text-uppercase text-muted small fw-semibold mb-4">Certificate of Completion</div>
            <h1 class="display-5 fw-bold mb-4">Online Classroom</h1>
            <p class="lead text-muted mb-2">This certifies that</p>
            <h2 class="display-6 fw-semibold mb-3">{{ $student->name }}</h2>
            <p class="lead text-muted mb-2">has successfully completed</p>
            <h3 class="fw-bold text-primary mb-4">{{ $course->title }}</h3>

            <div class="row justify-content-center g-4 mt-4">
                <div class="col-md-4">
                    <div class="small text-muted">Instructor</div>
                    <div class="fw-semibold">{{ $course->instructor->name ?? 'N/A' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Issued On</div>
                    <div class="fw-semibold">{{ now()->format('d M Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted">Certificate ID</div>
                    <div class="fw-semibold">CERT-{{ $course->id }}-{{ $student->id }}-{{ now()->format('Ymd') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.certificate-page {
    min-height: 720px;
}

@media print {
    .sidebar,
    .topbar,
    .no-print {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
    }

    .content-area {
        padding: 0 !important;
    }

    body {
        background: #fff !important;
    }

    .certificate-page {
        border: 0 !important;
        min-height: 100vh;
    }
}
</style>
@endpush
