@extends('layouts.app')

@section('title', 'My Time Logs')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Time Logs</h2>
            <p class="text-muted mb-0">View your attendance history</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('student.logs') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="bi bi-calendar-event me-1"></i> From Date
                    </label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="bi bi-calendar-check me-1"></i> To Date
                    </label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('student.logs') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table text-primary me-2"></i>
                    Daily Records
                </h5>
                <span class="badge bg-light text-dark rounded-pill">
                    {{ $paginator->total() }} entries
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="12%">Date</th>
                            <th width="10%">Day</th>
                            <th width="12%">AM IN</th>
                            <th width="12%">AM OUT</th>
                            <th width="12%">PM IN</th>
                            <th width="12%">PM OUT</th>
                            <th width="10%">Hours</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dayData as $day)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $day['date'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $day['day_name'] }}</span>
                                </td>
                                <td class="{{ $day['am_in_late'] ?? false ? 'text-warning' : '' }}">
                                    {{ $day['am_in'] ?? '--' }}
                                    @if($day['am_in_late'] ?? false)
                                        <i class="bi bi-exclamation-triangle-fill small"></i>
                                    @endif
                                </td>
                                <td>{{ $day['am_out'] ?? '--' }}</td>
                                <td class="{{ $day['pm_in_late'] ?? false ? 'text-warning' : '' }}">
                                    {{ $day['pm_in'] ?? '--' }}
                                    @if($day['pm_in_late'] ?? false)
                                        <i class="bi bi-exclamation-triangle-fill small"></i>
                                    @endif
                                </td>
                                <td>{{ $day['pm_out'] ?? '--' }}</td>
                                <td>
                                    <span class="fw-bold">{{ $day['hours'] }}</span>
                                </td>
                                <td>
                                    @if($day['status'] == 'COMPLETE')
                                        <span class="badge badge-success">
                                            <i class="bi bi-check-circle me-1"></i>Complete
                                        </span>
                                    @elseif($day['status'] == 'INCOMPLETE')
                                        <span class="badge badge-warning">
                                            <i class="bi bi-dash-circle me-1"></i>Incomplete
                                        </span>
                                    @elseif($day['status'] == 'SATURDAY')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-calendar-x me-1"></i>Saturday
                                        </span>
                                    @elseif($day['status'] == 'SUNDAY')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-calendar-x me-1"></i>Sunday
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="bi bi-x-circle me-1"></i>Absent
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No records found for the selected date range</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($paginator->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        {{ $paginator->links('pagination.bootstrap-5') }}
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>
@endsection
