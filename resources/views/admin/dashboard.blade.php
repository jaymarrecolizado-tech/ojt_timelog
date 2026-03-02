@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Overview</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->first_name }}!</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->format('F j, Y') }}
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 fw-medium">Total Students</p>
                        <h3 class="fw-bold mb-0">{{ $totalStudents }}</h3>
                        <small class="text-success">
                            <i class="bi bi-people-fill me-1"></i> Active enrollment
                        </small>
                    </div>
                    <div class="stat-icon primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 fw-medium">Present Today</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $presentToday }}</h3>
                        <small class="text-success">
                            <i class="bi bi-person-check-fill me-1"></i> Clocked in
                        </small>
                    </div>
                    <div class="stat-icon success">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 fw-medium">Absent Today</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ $absentToday }}</h3>
                        <small class="text-danger">
                            <i class="bi bi-person-x-fill me-1"></i> Not clocked in
                        </small>
                    </div>
                    <div class="stat-icon danger">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2 fw-medium">Late Today</p>
                        <h3 class="fw-bold mb-0 text-warning">{{ $lateCount }}</h3>
                        <small class="text-warning">
                            <i class="bi bi-clock-history me-1"></i> Late arrivals
                        </small>
                    </div>
                    <div class="stat-icon warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Currently Clocked In -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-fill text-primary me-2"></i>
                            Currently Clocked In
                        </h5>
                        <span class="badge bg-primary rounded-pill">{{ count($clockedIn) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 400px; overflow-y: auto;">
                        @if(count($clockedIn) > 0)
                            <div class="list-group list-group-flush">
                                @foreach($clockedIn as $student)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px;">
                                                    <i class="bi bi-person-fill text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $student['name'] }}</h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-card-text me-1"></i>{{ $student['student_id_no'] }}
                                                        <span class="mx-1">•</span>
                                                        <i class="bi bi-building me-1"></i>{{ $student['department'] }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success bg-opacity-10 text-success">
                                                    <i class="bi bi-clock me-1"></i>{{ $student['clocked_in_at'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="bi bi-person-x" style="font-size: 3rem;"></i>
                                </div>
                                <p class="text-muted mb-0">No students currently clocked in</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-activity text-primary me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted mb-0">Activity will appear here when students clock in/out</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-4">
        <div class="col-md-6">
            <a href="{{ route('admin.students') }}" class="card text-decoration-none h-100 quick-link-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-4" style="width: 64px; height: 64px;">
                            <i class="bi bi-people-fill text-primary" style="font-size: 1.75rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-semibold text-dark">Manage Students</h5>
                            <p class="text-muted mb-0">View, add, or edit student records</p>
                        </div>
                        <i class="bi bi-arrow-right text-primary ms-auto"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.reports') }}" class="card text-decoration-none h-100 quick-link-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-4" style="width: 64px; height: 64px;">
                            <i class="bi bi-file-earmark-bar-graph-fill text-success" style="font-size: 1.75rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-semibold text-dark">Generate Reports</h5>
                            <p class="text-muted mb-0">DTR and summary reports</p>
                        </div>
                        <i class="bi bi-arrow-right text-success ms-auto"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
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
    }

    .stat-card:nth-child(1)::before {
        background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
    }

    .stat-card:nth-child(2)::before {
        background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    }

    .stat-card:nth-child(3)::before {
        background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
    }

    .stat-card:nth-child(4)::before {
        background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
        color: #6366f1;
    }

    .stat-icon.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
        color: #10b981;
    }

    .stat-icon.danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
        color: #ef4444;
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
        color: #f59e0b;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid var(--gray-100);
        padding: 1rem 1.5rem;
        transition: background 0.2s ease;
    }

    .list-group-item:hover {
        background: var(--gray-50);
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .quick-link-card {
        transition: all 0.3s ease;
    }

    .quick-link-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .quick-link-card:hover .bi-arrow-right {
        transform: translateX(4px);
    }

    .quick-link-card .bi-arrow-right {
        transition: transform 0.3s ease;
    }
</style>
@endsection
