@extends('layouts.app')

@section('title', 'My Logs')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>My Time Logs</h2>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('student.logs') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('student.logs') }}" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daily Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>AM IN</th>
                            <th>AM OUT</th>
                            <th>PM IN</th>
                            <th>PM OUT</th>
                            <th>Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($days as $day)
                            <tr>
                                <td>{{ $day['date'] }}</td>
                                <td>{{ $day['day_name'] }}</td>
                                <td>{{ $day['am_in'] ?? '--' }}</td>
                                <td>{{ $day['am_out'] ?? '--' }}</td>
                                <td>{{ $day['pm_in'] ?? '--' }}</td>
                                <td>{{ $day['pm_out'] ?? '--' }}</td>
                                <td>{{ $day['hours'] }}</td>
                                <td>
                                    @if($day['status'] == 'COMPLETE')
                                        <span class="badge bg-success">Complete</span>
                                    @elseif($day['status'] == 'INCOMPLETE')
                                        <span class="badge bg-warning">Incomplete</span>
                                    @elseif($day['status'] == 'SATURDAY')
                                        <span class="badge bg-secondary">Saturday</span>
                                    @elseif($day['status'] == 'SUNDAY')
                                        <span class="badge bg-secondary">Sunday</span>
                                    @else
                                        <span class="badge bg-danger">Absent</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
