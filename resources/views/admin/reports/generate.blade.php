@extends('layouts.app')

@section('title', 'Generate Report')
@section('page-title', 'Generate Report')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="mb-4">Generate New Report</h5>

                @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <form action="{{ route('admin.reports.generate.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Report Type</label>
                        <select name="type" class="form-select" id="reportType" required>
                            <option value="">Select type...</option>
                            <option value="daily">Daily Report</option>
                            <option value="weekly">Weekly Report</option>
                            <option value="monthly">Monthly Report</option>
                            <option value="instructor">Instructor Performance Report</option>
                            <option value="session">Session Report</option>
                        </select>
                    </div>

                    <div id="dateField" class="mb-3 d-none">
                        <label class="form-label fw-medium">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div id="instructorFields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Instructor</label>
                            <select name="instructor" class="form-select">
                                <option value="">Select instructor...</option>
                                @foreach($instructors as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col">
                                <label class="form-label fw-medium">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
                            </div>
                            <div class="col">
                                <label class="form-label fw-medium">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>

                    <div id="sessionField" class="mb-3 d-none">
                        <label class="form-label fw-medium">Session</label>
                        <select name="session" class="form-select">
                            <option value="">Select session...</option>
                            @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->title }} — {{ $session->scheduled_at->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('reportType').addEventListener('change', function() {
    const type = this.value;
    document.getElementById('dateField').classList.toggle('d-none', type !== 'daily');
    document.getElementById('instructorFields').classList.toggle('d-none', type !== 'instructor');
    document.getElementById('sessionField').classList.toggle('d-none', type !== 'session');
});
</script>
@endpush
