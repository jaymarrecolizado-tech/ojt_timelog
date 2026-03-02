@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Profile</h2>
            <p class="text-muted mb-0">Manage your personal information</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Personal Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person text-primary me-2"></i>
                        Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-id-card me-1"></i> Student ID
                                </label>
                                <p class="info-value">{{ $student->student_id_no }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-person me-1"></i> Full Name
                                </label>
                                <p class="info-value">{{ $student->full_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-building me-1"></i> Department
                                </label>
                                <p class="info-value">{{ $student->department }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-book me-1"></i> Program
                                </label>
                                <p class="info-value">{{ $student->program }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-mortarboard me-1"></i> School/University
                                </label>
                                <p class="info-value">{{ $student->school_university ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-envelope me-1"></i> Email
                                </label>
                                <p class="info-value">{{ $student->user->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OJT Information Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase text-primary me-2"></i>
                        OJT Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-building me-1"></i> Company
                                </label>
                                <p class="info-value">{{ $student->company ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-person-badge me-1"></i> Supervisor
                                </label>
                                <p class="info-value">{{ $student->supervisor_name ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-calendar-event me-1"></i> OJT Start Date
                                </label>
                                <p class="info-value">{{ $student->ojt_start ? $student->ojt_start->format('F d, Y') : 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-calendar-check me-1"></i> OJT End Date
                                </label>
                                <p class="info-value">{{ $student->ojt_end ? $student->ojt_end->format('F d, Y') : 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-clock-history me-1"></i> Required Hours
                                </label>
                                <p class="info-value">{{ $student->required_hours }} hours</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="bi bi-info-circle me-1"></i> Status
                                </label>
                                <p class="info-value">
                                    @if($student->status == 'active')
                                        <span class="badge badge-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>Active
                                        </span>
                                    @elseif($student->status == 'completed')
                                        <span class="badge badge-primary">
                                            <i class="bi bi-award-fill me-1"></i>Completed
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-dash-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    <div class="profile-avatar mb-3">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $student->full_name }}</h5>
                    <p class="text-muted mb-2">{{ $student->student_id_no }}</p>
                    <span class="badge badge-primary">{{ ucfirst($student->status) }}</span>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-key me-1"></i> Change Password
                        </a>
                        <a href="{{ route('student.logs') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-check me-1"></i> View Logs
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-success">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .info-group {
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .info-group:hover {
        background: var(--gray-100);
    }

    .info-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: var(--gray-500);
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .profile-avatar i {
        font-size: 2rem;
        color: white;
    }
</style>
@endsection
