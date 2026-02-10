@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Dashboard Overview</h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 text-muted">
                        <i class="bi bi-people me-2"></i>
                        <small>Total Students</small>
                    </div>
                    <h3 class="mb-0">{{ $totalStudents }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 text-success">
                        <i class="bi bi-person-check me-2"></i>
                        <small>Present Today</small>
                    </div>
                    <h3 class="mb-0 text-success">{{ $presentToday }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 text-danger">
                        <i class="bi bi-person-x me-2"></i>
                        <small>Absent Today</small>
                    </div>
                    <h3 class="mb-0 text-danger">{{ $absentToday }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 text-warning">
                        <i class="bi bi-clock me-2"></i>
                        <small>Late Today</small>
                    </div>
                    <h3 class="mb-0 text-warning">{{ $lateCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Currently Clocked In -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Currently Clocked In ({{ count($clockedIn) }})</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(count($clockedIn) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($clockedIn as $student)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $student['name'] }}</h6>
                                            <small class="text-muted">{{ $student['student_id_no'] }} • {{ $student['department'] }}</small>
                                        </div>
                                        <span class="text-muted small">{{ $student['clocked_in_at'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No students currently clocked in</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center py-4">Activity will appear here when students clock in/out</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('admin.students') }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h5 class="mb-2">Manage Students</h5>
                    <p class="text-muted mb-0">View, add, or edit student records</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.reports') }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body">
                    <h5 class="mb-2">Generate Reports</h5>
                    <p class="text-muted mb-0">DTR and summary reports</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
