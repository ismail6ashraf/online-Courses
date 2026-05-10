@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card p-4" style="width:100%;max-width:420px">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                    style="width:80px;height:80px">
                    <i class="bi bi-mortarboard-fill text-white fs-4"></i>
                </div>
                <h4 class="fw-bold">Online Classroom</h4>
                <p class="text-muted small">Sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger small">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus placeholder="your@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        required placeholder="••••••••">
                </div>
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-medium">Sign In</button>
            </form>

            <hr class="my-3">
            <p class="text-center small text-muted">
                Don't have an account? <a href="{{ route('register') }}">Register</a>
            </p>
        </div>
    </div>
@endsection
