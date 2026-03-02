<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - OJT Time Log Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f1f5f9;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
            min-height: 100vh;
            color: var(--gray-800);
        }

        /* Modern Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gray-200);
            padding: 0.75rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-brand:hover {
            color: var(--primary-dark);
        }

        .navbar .nav-link {
            font-weight: 500;
            color: var(--gray-600);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 0.15rem;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.08);
        }

        .navbar .nav-link.btn-link {
            color: var(--gray-500);
            border: none;
            padding: 0.5rem 1rem;
            background: transparent !important;
        }

        .navbar .nav-link.btn-link:hover {
            color: var(--danger);
            background: rgba(239, 68, 68, 0.08) !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }

        /* Cards */
        .card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08),
                        0 4px 12px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08),
                        0 12px 24px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--gray-100);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-header h5,
        .card-header .card-title {
            margin: 0;
            font-weight: 600;
            color: var(--gray-800);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success);
        }

        .btn-success:hover {
            background: #059669;
            border-color: #059669;
        }

        .btn-danger {
            background: var(--danger);
            border-color: var(--danger);
        }

        .btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.925rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            border-bottom: 2px solid var(--gray-200);
            color: var(--gray-600);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            padding: 1rem;
            background: var(--gray-50);
        }

        .table tbody tr {
            transition: background 0.2s ease;
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-100);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.4em 0.75em;
            border-radius: 6px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.12);
            color: var(--warning);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.12);
            color: var(--info);
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.12);
            color: var(--primary);
        }

        /* Pagination */
        .pagination {
            gap: 0.5rem;
        }

        .page-link {
            border: none;
            color: var(--gray-600);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-color: transparent;
        }

        /* Toast Notifications */
        .toast-container {
            z-index: 9999;
        }

        .toast {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .toast-header {
            border-bottom: none;
            padding: 1rem 1.25rem 0.75rem;
        }

        .toast-header.bg-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%) !important;
            color: white;
        }

        .toast-header.bg-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%) !important;
            color: white;
        }

        .toast-body {
            padding: 0.75rem 1.25rem 1rem;
            color: var(--gray-700);
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card .stat-icon.primary {
            background: rgba(99, 102, 241, 0.12);
            color: var(--primary);
        }

        .stat-card .stat-icon.success {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
        }

        .stat-card .stat-icon.danger {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
        }

        .stat-card .stat-icon.warning {
            background: rgba(245, 158, 11, 0.12);
            color: var(--warning);
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            color: #065f46;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #991b1b;
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.12);
            color: #1e40af;
        }

        /* Text Colors */
        .text-primary { color: var(--primary) !important; }
        .text-success { color: var(--success) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-info { color: var(--info) !important; }
        .text-muted { color: var(--gray-500) !important; }

        /* List Group */
        .list-group-item {
            border: none;
            border-bottom: 1px solid var(--gray-100);
            padding: 1rem 1.25rem;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 16px;
        }

        .modal-header {
            border-bottom: 1px solid var(--gray-100);
            padding: 1.25rem 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--gray-100);
            padding: 1.25rem 1.5rem;
        }

        /* Dropdown */
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--gray-100);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                padding: 1rem;
                border-radius: 0 0 16px 16px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                margin-top: 0.5rem;
            }

            .navbar-nav {
                gap: 0.25rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @auth
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('student.dashboard') }}">
                <i class="bi bi-clock-history"></i>
                OJT TLMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @if(auth()->user()->isStudent())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                <i class="bi bi-grid me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.logs') ? 'active' : '' }}" href="{{ route('student.logs') }}">
                                <i class="bi bi-clock me-1"></i> Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.scan') ? 'active' : '' }}" href="{{ route('student.scan') }}">
                                <i class="bi bi-qr-code-scan me-1"></i> Scan QR
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}" href="{{ route('student.profile') }}">
                                <i class="bi bi-person me-1"></i> Profile
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-grid me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.students') || request()->routeIs('admin.student_detail') ? 'active' : '' }}" href="{{ route('admin.students') }}">
                                <i class="bi bi-people me-1"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports') || request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                                <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations') ? 'active' : '' }}" href="{{ route('admin.locations') }}">
                                <i class="bi bi-geo-alt me-1"></i> Locations
                            </a>
                        </li>
                        @if(auth()->user()->isSuperAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                                    <i class="bi bi-gear me-1"></i> Settings
                                </a>
                            </li>
                        @endif
                    @endif
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth

    <main>
        @yield('content')
    </main>

    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
