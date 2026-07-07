<span class="nav-section">Overview</span>
<a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"
    href="{{ route('student.dashboard') }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a class="nav-link {{ request()->routeIs('student.calendar') ? 'active' : '' }}"
    href="{{ route('student.calendar') }}">
    <i class="bi bi-calendar3"></i> Calendar
</a>
