@extends('layouts.app')

@section('title', 'Bulk DTR Generation')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Bulk DTR Generation</li>
                </ol>
            </nav>
            <h2>Bulk DTR Generation</h2>
            <p class="text-muted">Generate Daily Time Records for multiple students at once.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Options</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.bulk-dtr.generate') }}" method="POST">
                        @csrf

                        <!-- School/University Filter -->
                        <div class="mb-3">
                            <label for="school_university" class="form-label">School/University</label>
                            <select name="school_university" id="school_university" class="form-select">
                                <option value="">All Schools/Universities</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school }}">{{ $school }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select name="department" id="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" name="from_date" id="from_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" name="to_date" id="to_date" class="form-control" required>
                            </div>
                        </div>

                        <!-- Output Format -->
                        <div class="mb-4">
                            <label class="form-label">Output Format</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="output_format" id="format_single" value="single" checked>
                                <label class="form-check-label" for="format_single">
                                    <strong>Single PDF</strong> - All students in one combined document
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="output_format" id="format_zip" value="zip">
                                <label class="form-check-label" for="format_zip">
                                    <strong>ZIP File</strong> - Separate PDF for each student
                                </label>
                            </div>
                        </div>

                        <!-- Quick Select Buttons -->
                        <div class="mb-4">
                            <label class="form-label d-block">Quick Date Selection</label>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_month')">Last Month</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('this_week')">This Week</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_week')">Last Week</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Generate Bulk DTR
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Information</h6>
                </div>
                <div class="card-body">
                    <h6>Filter Options:</h6>
                    <ul class="small">
                        <li><strong>School/University:</strong> Filter by specific institution</li>
                        <li><strong>Department:</strong> Filter by department</li>
                        <li><strong>Status:</strong> Active, Completed, Inactive, or Pending</li>
                    </ul>
                    <h6>Output Formats:</h6>
                    <ul class="small">
                        <li><strong>Single PDF:</strong> One document with all students, page breaks between students</li>
                        <li><strong>ZIP File:</strong> Individual PDF files for each student, bundled together</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Leave all filters empty to include ALL students</li>
                        <li>Use quick date buttons for common date ranges</li>
                        <li>Single PDF is recommended for printing multiple DTRs</li>
                        <li>ZIP format is ideal for emailing individual DTRs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setDateRange(range) {
    const today = new Date();
    const fromDate = document.getElementById('from_date');
    const toDate = document.getElementById('to_date');

    let start, end;

    switch(range) {
        case 'this_month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'last_month':
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'this_week':
            const day = today.getDay();
            start = new Date(today);
            start.setDate(today.getDate() - day);
            end = new Date(today);
            end.setDate(today.getDate() - day + 6);
            break;
        case 'last_week':
            start = new Date(today);
            start.setDate(today.getDate() - today.getDay() - 7);
            end = new Date(today);
            end.setDate(today.getDate() - today.getDay() - 1);
            break;
    }

    fromDate.value = start.toISOString().split('T')[0];
    toDate.value = end.toISOString().split('T')[0];
}
</script>
@endsection
