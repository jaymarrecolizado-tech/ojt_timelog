@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $student->first_name }}!
            </h2>
            <p class="text-muted mb-0">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                <i class="bi bi-clock me-1"></i> {{ $currentStatus }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Today's Attendance Card -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-day text-primary me-2"></i>
                            Today's Attendance
                        </h5>
                        <span class="badge bg-light text-dark rounded-pill">{{ now()->format('M j, Y') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Time Slots -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="time-slot-card">
                                <div class="time-slot-label">AM IN</div>
                                <div class="time-slot-value">
                                    @php
                                        $amIn = $todayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                                        echo $amIn ? $amIn->timestamp->format('h:i A') : '--:--';
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="time-slot-card">
                                <div class="time-slot-label">AM OUT</div>
                                <div class="time-slot-value">
                                    @php
                                        $amOut = $todayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                                        echo $amOut ? $amOut->timestamp->format('h:i A') : '--:--';
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="time-slot-card">
                                <div class="time-slot-label">PM IN</div>
                                <div class="time-slot-value">
                                    @php
                                        $pmIn = $todayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                                        echo $pmIn ? $pmIn->timestamp->format('h:i A') : '--:--';
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="time-slot-card">
                                <div class="time-slot-label">PM OUT</div>
                                <div class="time-slot-value">
                                    @php
                                        $pmOut = $todayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');
                                        echo $pmOut ? $pmOut->timestamp->format('h:i A') : '--:--';
                                    @endphp
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Hours -->
                    <div class="today-hours">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="bi bi-clock-history me-1"></i> Hours logged today
                            </span>
                            <span class="today-hours-value">{{ $hoursToday }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OJT Progress Card -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        OJT Progress
                    </h5>
                </div>
                <div class="card-body">
                    <div class="progress-circle mb-3">
                        <div class="progress-percentage">{{ $completionPercentage }}%</div>
                        <svg class="progress-ring" width="120" height="120">
                            <circle class="progress-ring-bg" cx="60" cy="60" r="50"/>
                            <circle class="progress-ring-fill" cx="60" cy="60" r="50"
                                    style="stroke-dasharray: {{ min($completionPercentage, 100) * 3.14 }}, 314"/>
                        </svg>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="progress-stat">
                                <div class="progress-stat-value">{{ number_format($accumulatedHours, 1) }}</div>
                                <div class="progress-stat-label">Hours Done</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="progress-stat">
                                <div class="progress-stat-value">{{ number_format($remainingHours, 1) }}</div>
                                <div class="progress-stat-label">Remaining</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan QR Button -->
    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('student.scan') }}" class="btn btn-primary btn-lg w-100 scan-btn">
                <i class="bi bi-qr-code-scan me-2"></i>
                SCAN QR CODE TO CLOCK IN/OUT
            </a>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('student.logs') }}" class="card text-decoration-none h-100 quick-link-card">
                <div class="card-body text-center">
                    <div class="quick-link-icon bg-primary bg-opacity-10">
                        <i class="bi bi-calendar-check text-primary"></i>
                    </div>
                    <h6 class="mb-0 mt-3 text-dark">View Logs</h6>
                    <p class="text-muted small mb-0">See your attendance history</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('student.profile') }}" class="card text-decoration-none h-100 quick-link-card">
                <div class="card-body text-center">
                    <div class="quick-link-icon bg-success bg-opacity-10">
                        <i class="bi bi-person text-success"></i>
                    </div>
                    <h6 class="mb-0 mt-3 text-dark">Profile</h6>
                    <p class="text-muted small mb-0">Manage your account</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="quick-link-icon bg-warning bg-opacity-10">
                        <i class="bi bi-clock-history text-warning"></i>
                    </div>
                    <h6 class="mb-0 mt-3">Required Hours</h6>
                    <p class="text-dark fw-bold mb-0">{{ $student->required_hours }} hrs</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .time-slot-card {
        background: var(--gray-50);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .time-slot-card:hover {
        background: var(--gray-100);
        transform: translateY(-2px);
    }

    .time-slot-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        margin-bottom: 0.5rem;
    }

    .time-slot-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    .today-hours {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%);
        border-radius: 12px;
        padding: 1rem 1.5rem;
    }

    .today-hours-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }

    .progress-circle {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .progress-percentage {
        position: absolute;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
    }

    .progress-ring {
        transform: rotate(-90deg);
    }

    .progress-ring-bg {
        fill: none;
        stroke: var(--gray-200);
        stroke-width: 8;
    }

    .progress-ring-fill {
        fill: none;
        stroke: url(#gradient);
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dasharray 0.5s ease;
    }

    .progress-stat {
        padding: 0.5rem;
    }

    .progress-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    .progress-stat-label {
        font-size: 0.75rem;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .scan-btn {
        padding: 1.25rem;
        font-weight: 600;
        letter-spacing: 0.025em;
        border-radius: 16px;
    }

    .quick-link-card {
        transition: all 0.3s ease;
    }

    .quick-link-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .quick-link-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .quick-link-icon i {
        font-size: 1.75rem;
    }

    /* SVG Gradient */
    .progress-ring-fill {
        stroke: #6366f1;
    }
</style>

<svg width="0" height="0">
    <defs>
        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#6366f1"/>
            <stop offset="100%" style="stop-color:#8b5cf6"/>
        </linearGradient>
    </defs>
</svg>
@endsection
