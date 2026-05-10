<!DOCTYPE html>
<html lang="en" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Online Classroom') — Online Classroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --sidebar-bg: #1e1b4b;
            --sidebar-text: #c7d2fe;
            --sidebar-active: #4f46e5;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform .3s;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-link {
            color: var(--sidebar-text);
            padding: .6rem 1.25rem;
            border-radius: .375rem;
            margin: .15rem .75rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: .625rem;
            transition: all .2s;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
        }

        .sidebar-nav .nav-section {
            color: rgba(199, 210, 254, .4);
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            padding: .75rem 1.25rem .25rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .875rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .content-area {
            padding: 1.5rem;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .stat-card {
            border-radius: .75rem;
            padding: 1.25rem;
            color: #fff;
        }

        .badge-role-admin {
            background: #dc2626;
        }

        .badge-role-instructor {
            background: #7c3aed;
        }

        .badge-role-student {
            background: #0891b2;
        }

        .alert-severity-critical {
            border-left: 4px solid #dc2626;
        }

        .alert-severity-warning {
            border-left: 4px solid #f59e0b;
        }

        .alert-severity-info {
            border-left: 4px solid #3b82f6;
        }

        @media(max-width:768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    @auth
        <div class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <h5><i class="bi bi-mortarboard-fill me-2"></i>Online Classroom</h5>
                <small class="text-muted" style="color:rgba(199,210,254,.5)!important">
                    {{ ucfirst(auth()->user()->role) }} Panel
                </small>
            </div>
            <nav class="sidebar-nav">
                @include('layouts.partials.sidebar-' . auth()->user()->role)
            </nav>
        </div>

        <div class="main-content">
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm d-md-none"
                        onclick="document.getElementById('sidebar').classList.toggle('show')">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h6 class="page-title">@yield('page-title', 'Dashboard')</h6>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @php $unreadAlerts = \App\Models\Alert::where('target_user_id', auth()->id())->where('is_read', false)->count(); @endphp
                    @if($unreadAlerts > 0)
                        <span class="badge bg-danger rounded-pill">{{ $unreadAlerts }} alerts</span>
                    @endif
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                style="width:32px;height:32px;font-size:.8rem">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">{{ auth()->user()->email }}</h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i
                                            class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    @else
        @yield('content')
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>

</html>
