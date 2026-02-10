@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $student->first_name }}!</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">TODAY - {{ now()->format('F d, Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted">Status:</span>
                        <span class="fw-bold">{{ $currentStatus }}</span>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded d-flex justify-content-between">
                                <span>AM IN</span>
                                <span class="fw-bold">
                                    @php
                                        $amIn = $todayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                                        echo $amIn ? $amIn->timestamp->format('h:i A') : '--';
                                    @endphp
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded d-flex justify-content-between">
                                <span>AM OUT</span>
                                <span class="fw-bold">
                                    @php
                                        $amOut = $todayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                                        echo $amOut ? $amOut->timestamp->format('h:i A') : '--';
                                    @endphp
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded d-flex justify-content-between">
                                <span>PM IN</span>
                                <span class="fw-bold">
                                    @php
                                        $pmIn = $todayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                                        echo $pmIn ? $pmIn->timestamp->format('h:i A') : '--';
                                    @endphp
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded d-flex justify-content-between">
                                <span>PM OUT</span>
                                <span class="fw-bold">
                                    @php
                                        $pmOut = $todayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');
                                        echo $pmOut ? $pmOut->timestamp->format('h:i A') : '--';
                                    @endphp
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-muted">
                        Hours today: <span class="fw-bold">{{ $hoursToday }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>OJT Progress</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-primary">{{ $completionPercentage }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ min($completionPercentage, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ number_format($accumulatedHours, 1) }} hrs done</span>
                        <span>{{ number_format($remainingHours, 1) }} hrs remaining</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('student.scan') }}" class="btn btn-primary btn-lg w-100 py-3">
                <i class="bi bi-camera me-2"></i>SCAN QR CODE
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('student.logs') }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check fs-1 text-primary mb-2"></i>
                    <h6 class="mb-0">View Logs</h6>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('student.profile') }}" class="card text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person fs-1 text-primary mb-2"></i>
                    <h6 class="mb-0">Profile</h6>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history fs-1 text-primary mb-2"></i>
                    <h6 class="mb-0">Required: {{ $student->required_hours }} hrs</h6>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
