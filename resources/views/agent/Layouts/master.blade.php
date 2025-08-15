<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>@yield('title','Agent Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: #f6f8fb; }
        .card { border: none; border-radius: 1rem; }
        .stat-card .icon { font-size: 1.8rem; opacity: .75; }
        .sidebar {
            width: 260px; min-height: 100vh; position: sticky; top: 0;
            background: #111827; color: #fff;
        }
        .sidebar a { color: #cbd5e1; text-decoration: none; }
        .sidebar a.active, .sidebar a:hover { color: #fff; }
    </style>

    @stack('head')
</head>
<body>
<div class="d-flex">

    {{-- Sidebar --}}
    <aside class="sidebar p-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-headset fs-3"></i>
            <h5 class="m-0">Agent Panel</h5>
        </div>
        <hr class="border-secondary">
        <nav class="nav flex-column gap-2">
            <a class="nav-link px-0 {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}"
               href="{{ route('agent.dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link px-0" href="#">
                <i class="bi bi-chat-dots me-2"></i> My Chats
            </a>
            <a class="nav-link px-0" href="#">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </nav>
        <div class="mt-auto pt-3">
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-grow-1">
        {{-- Topbar --}}
        <header class="bg-white border-bottom">
            <div class="container-fluid py-3 d-flex justify-content-between align-items-center">
                <h4 class="m-0">@yield('page_title','Dashboard')</h4>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">
                        {{ optional(Auth::guard('agent')->user())->name }}
                    </span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(optional(Auth::guard('agent')->user())->name ?? 'Agent') }}"
                         class="rounded-circle" width="36" height="36" alt="Avatar">
                </div>
            </div>
        </header>

        <main class="container-fluid py-4">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
