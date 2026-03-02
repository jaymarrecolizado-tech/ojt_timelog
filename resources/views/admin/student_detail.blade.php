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
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h2 class="mb-0">{{ $student->full_name }}</h2>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label for="month" class="form-label mb-0 text-nowrap">Month:</label>
                        <input type="month" id="month" name="month" class="form-control" value="{{ $month ?? now()->format('Y-m') }}" style="width: 180px;">
                        <button type="submit" class="btn btn-outline-primary text-nowrap">
                            <i class="bi bi-calendar3 me-1"></i>View
                        </button>
                    </form>
                    <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#editStudentModal">
                        <i class="bi bi-pencil me-1"></i>Edit
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
                                        // Cap daily hours at 8
                                        $hours = min($hours, 8);

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
                        {{ $logsPaginated->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.students.update', $student->id) }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $student->first_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ $student->middle_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $student->last_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ $student->department }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program</label>
                            <input type="text" name="program" class="form-control" value="{{ $student->program }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact No.</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ $student->contact_no }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" value="{{ $student->company }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supervisor Name</label>
                            <input type="text" name="supervisor_name" class="form-control" value="{{ $student->supervisor_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">OJT Start Date</label>
                            <input type="date" name="ojt_start" class="form-control" value="{{ $student->ojt_start?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">OJT End Date</label>
                            <input type="date" name="ojt_end" class="form-control" value="{{ $student->ojt_end?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Required Hours</label>
                            <input type="number" name="required_hours" class="form-control" value="{{ $student->required_hours }}" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $student->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $student->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="completed" {{ $student->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Manual Log Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.students.logs.create', $student->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Manual Time Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required max="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="log_type" class="form-select" required>
                            <option value="IN">IN</option>
                            <option value="OUT">OUT</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="log_category" class="form-select" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
