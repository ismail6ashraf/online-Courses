@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h1 class="text-center mb-4">Choose Your Plan</h1>

        @if(session('error'))
            <div class="alert alert-danger text-center">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            @foreach($plans as $plan)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-body text-center">
                            <h3>{{ $plan->name }}</h3>
                            @if($currentPlanId == $plan->id)
                                <span class="badge bg-success mb-2">
                                    Active Plan
                                </span>
                            @endif
                            <h2 class="my-3">
                                @if($plan->price == 0)
                                    <span class="text-success">FREE</span>
                                @else
                                    ${{ number_format($plan->price, 2) }}
                                    <small class="text-muted">/ month</small>
                                @endif
                            </h2>
                            <p>
                                Courses:
                                {{ $plan->max_courses ?? 'Unlimited' }}
                            </p>

                            <p>
                                Students:
                                {{ $plan->max_students ?? 'Unlimited' }}
                            </p>

                            <ul class="list-unstyled">
                                @foreach($plan->features ?? [] as $feature)
                                    <li>✅ {{ $feature }}</li>
                                @endforeach
                            </ul>

                            @auth
                                @if($currentPlanId == $plan->id)

                                    <button class="btn btn-success w-100" disabled>
                                        ✓ Current Plan
                                    </button>

                                @else

                                    @if($plan->price == 0)

                                        <form method="POST" action="{{ route('instructor.pricing.subscribe', $plan) }}">
                                            @csrf

                                            <button class="btn btn-success w-100">
                                                Activate Free Plan
                                            </button>
                                        </form>

                                    @else

                                        <a href="{{ route('instructor.checkout', $plan) }}" class="btn btn-primary w-100">
                                            {{ $currentPlanId ? 'Upgrade Plan' : 'Subscribe Now' }}
                                        </a>

                                    @endif

                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                    Login to Subscribe
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
