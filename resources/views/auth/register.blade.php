@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card p-4" style="width:100%;max-width:480px">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                    style="width:80px;height:80px">
                    <i class="bi bi-mortarboard-fill text-white fs-4"></i>
                </div>
                <h4 class="fw-bold">Create Account</h4>
            </div>

            @if($errors->any())
                <div class="alert alert-danger small">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-medium">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Phone (Optional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">Select role</option>
                        <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-medium">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-medium">Create Account</button>
            </form>
            <p class="text-center small text-muted mt-3">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
@endsection
