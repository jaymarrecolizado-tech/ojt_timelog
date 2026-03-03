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
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <h2 class="mb-0">{{ $student->full_name }}</h2>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <label for="month" class="form-label mb-0 text-nowrap">Month:</label>
                            <input type="month" id="month" name="month" class="form-control"
                                value="{{ $month ?? now()->format('Y-m') }}" style="width: 180px;">
                            <button type="submit" class="btn btn-outline-primary text-nowrap">
                                <i class="bi bi-calendar3 me-1"></i>View
                            </button>
                        </form>
                        <button class="btn btn-primary text-nowrap" data-bs-toggle="modal"
                            data-bs-target="#editStudentModal">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left: Student Info --}}
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
                                    <span
                                        class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'completed' ? 'primary' : 'secondary') }}">
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

            {{-- Right: Time Logs --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Time Logs
                            <span
                                class="badge bg-light text-dark ms-1">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span>
                        </h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Manual Entry
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>AM IN</th>
                                        <th>AM OUT</th>
                                        <th>PM IN</th>
                                        <th>PM OUT</th>
                                        <th>Hours</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $date => $dayLogs)
                                        @php
                                            $amIn = $dayLogs->first(fn($l) => $l->log_category === 'AM' && $l->log_type === 'IN');
                                            $amOut = $dayLogs->first(fn($l) => $l->log_category === 'AM' && $l->log_type === 'OUT');
                                            $pmIn = $dayLogs->first(fn($l) => $l->log_category === 'PM' && $l->log_type === 'IN');
                                            $pmOut = $dayLogs->first(fn($l) => $l->log_category === 'PM' && $l->log_type === 'OUT');

                                            // Accurate minute-based calculation
                                            $totalMins = 0;
                                            if ($amIn && $amOut) {
                                                $totalMins += \Carbon\Carbon::parse($amIn->timestamp)
                                                    ->diffInMinutes(\Carbon\Carbon::parse($amOut->timestamp));
                                            }
                                            if ($pmIn && $pmOut) {
                                                $totalMins += \Carbon\Carbon::parse($pmIn->timestamp)
                                                    ->diffInMinutes(\Carbon\Carbon::parse($pmOut->timestamp));
                                            }
                                            // Cap at 8 hours = 480 minutes
                                            $totalMins = min($totalMins, 480);
                                            $hoursDisp = floor($totalMins / 60);
                                            $minsDisp = $totalMins % 60;
                                            $hoursLabel = $totalMins > 0
                                                ? $hoursDisp . 'h ' . str_pad($minsDisp, 2, '0', STR_PAD_LEFT) . 'm'
                                                : '--';

                                            $displayDate = \Carbon\Carbon::parse(explode(' ', $date)[0])->format('M d, Y');
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $displayDate }}</td>

                                            {{-- AM IN --}}
                                            <td>
                                                @if($amIn)
                                                    {{ \Carbon\Carbon::parse($amIn->timestamp)->format('h:i A') }}
                                                    @if($amIn->is_manual)
                                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"
                                                            title="Manual entry">M</span>
                                                    @endif
                                                    @if($amIn->is_flagged)
                                                        <span class="badge bg-danger ms-1" style="font-size:0.65rem;"
                                                            title="{{ $amIn->flag_reason }}">!</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>

                                            {{-- AM OUT --}}
                                            <td>
                                                @if($amOut)
                                                    {{ \Carbon\Carbon::parse($amOut->timestamp)->format('h:i A') }}
                                                    @if($amOut->is_manual)
                                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"
                                                            title="Manual entry">M</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>

                                            {{-- PM IN --}}
                                            <td>
                                                @if($pmIn)
                                                    {{ \Carbon\Carbon::parse($pmIn->timestamp)->format('h:i A') }}
                                                    @if($pmIn->is_manual)
                                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"
                                                            title="Manual entry">M</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>

                                            {{-- PM OUT --}}
                                            <td>
                                                @if($pmOut)
                                                    {{ \Carbon\Carbon::parse($pmOut->timestamp)->format('h:i A') }}
                                                    @if($pmOut->is_manual)
                                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"
                                                            title="Manual entry">M</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>

                                            {{-- Hours --}}
                                            <td>
                                                @if($totalMins > 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold px-2">
                                                        {{ $hoursLabel }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>

                                            {{-- Actions: delete each individual log in this day --}}
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                                    @foreach($dayLogs as $log)
                                                        <form method="POST"
                                                            action="{{ route('admin.students.logs.delete', [$student->id, $log->id]) }}"
                                                            onsubmit="return confirm('Delete {{ $log->log_category }} {{ $log->log_type }} ({{ \Carbon\Carbon::parse($log->timestamp)->format('h:i A') }}) on {{ $displayDate }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="month" value="{{ $month }}">
                                                            <button type="submit" class="btn btn-xs py-0 px-1 btn-outline-danger"
                                                                title="Delete {{ $log->log_category }}-{{ $log->log_type }}"
                                                                style="font-size:0.7rem;">
                                                                <i class="bi bi-trash"></i>
                                                                {{ $log->log_category }}{{ $log->log_type }}
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                                                <p class="mb-0 mt-2">No logs found for
                                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($logsPaginated->hasPages())
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing {{ $logsPaginated->firstItem() ?? 0 }} to {{ $logsPaginated->lastItem() ?? 0 }}
                                of {{ $logsPaginated->total() }} entries
                            </div>
                            {{ $logsPaginated->appends(['month' => $month])->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                </div>

                {{-- Legend --}}
                <div class="d-flex gap-3 mt-2 flex-wrap">
                    <small class="text-muted"><span class="badge bg-warning text-dark">M</span> Manual entry</small>
                    <small class="text-muted"><span class="badge bg-danger">!</span> Flagged entry</small>
                    <small class="text-muted"><i class="bi bi-clock text-success"></i> Hours capped at 8h/day</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Edit Student Modal --}}
    {{-- ================================================================ --}}
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
                                <input type="text" name="first_name" class="form-control" value="{{ $student->first_name }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control"
                                    value="{{ $student->middle_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ $student->last_name }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" value="{{ $student->department }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Program</label>
                                <input type="text" name="program" class="form-control" value="{{ $student->program }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact No.</label>
                                <input type="text" name="contact_no" class="form-control"
                                    value="{{ $student->contact_no }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company</label>
                                <input type="text" name="company" class="form-control" value="{{ $student->company }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supervisor Name</label>
                                <input type="text" name="supervisor_name" class="form-control"
                                    value="{{ $student->supervisor_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">OJT Start Date</label>
                                <input type="date" name="ojt_start" class="form-control"
                                    value="{{ $student->ojt_start?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">OJT End Date</label>
                                <input type="date" name="ojt_end" class="form-control"
                                    value="{{ $student->ojt_end?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Required Hours</label>
                                <input type="number" name="required_hours" class="form-control"
                                    value="{{ $student->required_hours }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ $student->status == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ $student->status == 'inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                    <option value="completed" {{ $student->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
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

    {{-- ================================================================ --}}
    {{-- Add Manual Log Modal --}}
    {{-- ================================================================ --}}
    <div class="modal fade" id="addLogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.students.logs.create', $student->id) }}">
                    @csrf
                    {{-- Preserve month so redirect returns to same month --}}
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Add Manual Time Log
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Validation errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger py-2">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" required max="{{ now()->toDateString() }}"
                                value="{{ old('date') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                            <input type="time" name="time" class="form-control" required value="{{ old('time') }}">
                            <div class="form-text">24-hour format. e.g. 08:00 for 8 AM</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Scan Type <span class="text-danger">*</span></label>
                            <select name="_scan_type" id="scanTypeSelect" class="form-select" required
                                onchange="applyScanType(this.value)">
                                <option value="" disabled selected>— Select scan type —</option>
                                <option value="AM|IN" {{ old('log_category') == 'AM' && old('log_type') == 'IN' ? 'selected' : '' }}>AM IN &nbsp;(Morning arrival)</option>
                                <option value="AM|OUT" {{ old('log_category') == 'AM' && old('log_type') == 'OUT' ? 'selected' : '' }}>AM OUT (Morning departure)</option>
                                <option value="PM|IN" {{ old('log_category') == 'PM' && old('log_type') == 'IN' ? 'selected' : '' }}>PM IN &nbsp;(Afternoon arrival)</option>
                                <option value="PM|OUT" {{ old('log_category') == 'PM' && old('log_type') == 'OUT' ? 'selected' : '' }}>PM OUT (Afternoon departure)</option>
                            </select>
                            {{-- Hidden fields the controller actually reads --}}
                            <input type="hidden" name="log_category" id="logCategory" value="{{ old('log_category') }}">
                            <input type="hidden" name="log_type" id="logType" value="{{ old('log_type') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Location</label>
                            <select name="location_id" class="form-select">
                                <option value="">— None / Manual —</option>
                                @foreach(\App\Models\Location::where('is_active', true)->orderBy('name')->get() as $loc)
                                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reason / Notes <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="2" required
                                placeholder="Reason for manual entry (e.g. forgot to scan, system error)">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Add Log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function applyScanType(val) {
            if (!val) return;
            var parts = val.split('|');
            document.getElementById('logCategory').value = parts[0];
            document.getElementById('logType').value = parts[1];
        }

        // If there are validation errors, re-open the modal automatically
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                var modal = new bootstrap.Modal(document.getElementById('addLogModal'));
                modal.show();
            });
        @endif
    </script>
@endpush