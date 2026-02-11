@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Reports</h2>
        </div>
    </div>

    <div class="row g-4">
        <!-- DTR Report -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Daily Time Record</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Generate Daily Time Record (DTR) for students showing all clock-in/out times.</p>
                    <form action="{{ route('admin.reports') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Choose a student...</option>
                                @foreach(\App\Models\Student::all() as $s)
                                    <option value="{{ $s->id }}">{{ $s->student_id_no }} - {{ $s->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">From</label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">To</label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Generate DTR</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">View attendance statistics for all students in a date range.</p>
                    <form action="{{ route('admin.reports') }}" method="GET">
                        <input type="hidden" name="type" value="attendance">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">From</label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">To</label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach(\App\Models\Student::distinct()->pluck('department') as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Generate Summary</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- OJT Progress -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>OJT Progress</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Track OJT completion progress for all students.</p>
                    <a href="{{ route('admin.reports') }}?type=progress" class="btn btn-outline-primary w-100">View Progress Report</a>
                </div>
            </div>
        </div>

        <!-- Late Comers -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Late Comers</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">View list of students who arrived late.</p>
                    <a href="{{ route('admin.reports') }}?type=late" class="btn btn-outline-warning w-100">View Late Report</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
