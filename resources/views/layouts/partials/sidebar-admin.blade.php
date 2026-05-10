<span class="nav-section">Overview</span>
<a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<span class="nav-section">Management</span>
<a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
    <i class="bi bi-people"></i> Users
</a>
<a class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}" href="{{ route('admin.sessions.index') }}">
    <i class="bi bi-camera-video"></i> Sessions
</a>

<span class="nav-section">Monitoring</span>
<a class="nav-link {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}" href="{{ route('admin.alerts.index') }}">
    <i class="bi bi-bell"></i> Alerts
    @php $c = \App\Models\Alert::where('is_read',false)->count(); @endphp
    @if($c > 0)<span class="badge bg-danger rounded-pill ms-auto">{{ $c }}</span>@endif
</a>
<a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
    <i class="bi bi-bar-chart"></i> Reports
</a>
