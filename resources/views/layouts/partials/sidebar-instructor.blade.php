<span class="nav-section">Overview</span>
<a class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}"
    href="{{ route('instructor.dashboard') }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a class="nav-link {{ request()->routeIs('instructor.calendar') ? 'active' : '' }}"
    href="{{ route('instructor.calendar') }}">
    <i class="bi bi-calendar3"></i> Calendar
</a>

<span class="nav-section">Teaching</span>
<a class="nav-link {{ request()->routeIs('instructor.sessions.*') ? 'active' : '' }}"
    href="{{ route('instructor.sessions.index') }}">
    <i class="bi bi-camera-video"></i> My Sessions
</a>

<a class="nav-link {{ request()->routeIs('instructor.assessments.*') ? 'active' : '' }}"
    href="{{ route('instructor.assessments.index') }}">
    <i class="bi bi-clipboard-check"></i> Assessments
</a>

<a class="nav-link {{ request()->routeIs('instructor.courses.*') ? 'active' : '' }}"
    href="{{ route('instructor.courses.index') }}">
    <i class="bi bi-book"></i> My Courses
</a>

<span class="nav-section">Business</span>

<a class="nav-link {{ request()->routeIs('instructor.pricing*') ? 'active' : '' }}"
    href="{{ route('instructor.pricing') }}">
    <i class="bi bi-credit-card"></i> Billing & Subscription
</a>

<span class="nav-section">Tasks & Alerts</span>

<a class="nav-link {{ request()->routeIs('instructor.tasks.*') ? 'active' : '' }}"
    href="{{ route('instructor.tasks.index') }}">
    <i class="bi bi-list-task"></i> My Tasks

    @php
        $c = \App\Models\InstructorTask::where('instructor_id', auth()->id())
            ->where('status', 'pending')
            ->count();
    @endphp

    @if($c > 0)
        <span class="badge bg-warning text-dark rounded-pill ms-auto">
            {{ $c }}
        </span>
    @endif
</a>
