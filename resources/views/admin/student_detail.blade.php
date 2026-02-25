@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.students') }}">Students</a></li>
                    <li class="breadcrumb-item active">{{ $student->full_name }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">{{ $student->full_name }}</h2>
                <div class="d-flex align-items-center gap-3">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label for="month" class="form-label mb-0">Month:</label>
                        <input type="month" id="month" name="month" class="form-control" value="{{ $month ?? now()->format('Y-m') }}" style="width: 180px;">
                        <button type="submit" class="btn btn-outline-primary">View</button>
                    </form>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editStudentModal">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Student ID:</td>
                            <td class="fw-bold">{{ $student->student_id_no }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Department:</td>
                            <td>{{ $student->department }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Program:</td>
                            <td>{{ $student->program }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $student->user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Contact:</td>
                            <td>{{ $student->contact_no ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'completed' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">OJT Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Company:</td>
                            <td>{{ $student->company ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Supervisor:</td>
                            <td>{{ $student->supervisor_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Start Date:</td>
                            <td>{{ $student->ojt_start ? $student->ojt_start->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">End Date:</td>
                            <td>{{ $student->ojt_end ? $student->ojt_end->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Required Hours:</td>
                            <td class="fw-bold">{{ $student->required_hours }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Time Logs</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
                        <i class="bi bi-plus-lg me-2"></i>Add Manual Entry
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>AM IN</th>
                                    <th>AM OUT</th>
                                    <th>PM IN</th>
                                    <th>PM OUT</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $date => $dayLogs)
                                    @php
                                        $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                                        $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                                        $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                                        $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');
                                        
                                        $hours = 0;
                                        if ($amIn && $amOut) $hours += \Carbon\Carbon::parse($amIn->timestamp)->diffInHours(\Carbon\Carbon::parse($amOut->timestamp));
                                        if ($pmIn && $pmOut) $hours += \Carbon\Carbon::parse($pmIn->timestamp)->diffInHours(\Carbon\Carbon::parse($pmOut->timestamp));
                                        
                                        $displayDate = \Carbon\Carbon::parse(explode(' ', $date)[0])->format('M d, Y');
                                    @endphp
                                    <tr>
                                        <td>{{ $displayDate }}</td>
                                        <td>{{ $amIn ? \Carbon\Carbon::parse($amIn->timestamp)->format('h:i A') : '--' }}</td>
                                        <td>{{ $amOut ? \Carbon\Carbon::parse($amOut->timestamp)->format('h:i A') : '--' }}</td>
                                        <td>{{ $pmIn ? \Carbon\Carbon::parse($pmIn->timestamp)->format('h:i A') : '--' }}</td>
                                        <td>{{ $pmOut ? \Carbon\Carbon::parse($pmOut->timestamp)->format('h:i A') : '--' }}</td>
                                        <td>{{ $hours }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No logs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logsPaginated->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $logsPaginated->firstItem() ?? 0 }} to {{ $logsPaginated->lastItem() ?? 0 }} of {{ $logsPaginated->total() }} entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{ $logsPaginated->links() }}
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
