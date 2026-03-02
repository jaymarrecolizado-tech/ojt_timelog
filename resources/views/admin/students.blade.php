@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manage Students</h2>
            <p class="text-muted mb-0">View and manage student accounts</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="bi bi-person-plus me-1"></i> Add Student
        </button>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.students') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">
                        <i class="bi bi-search me-1"></i> Search
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or ID..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="bi bi-funnel me-1"></i> Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people text-primary me-2"></i>
                    Student List
                </h5>
                <span class="badge bg-light text-dark rounded-pill">
                    {{ $students->total() }} students
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="15%">Student ID</th>
                            <th width="25%">Name</th>
                            <th width="18%">Department</th>
                            <th width="18%">Program</th>
                            <th width="12%">Status</th>
                            <th width="12%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $student->student_id_no }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-fill text-primary small"></i>
                                        </div>
                                        <span>{{ $student->full_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $student->department }}</span>
                                </td>
                                <td>{{ $student->program }}</td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="{{ route('admin.students.detail', $student->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No students found matching your criteria</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($students->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        {{ $students->links('pagination.bootstrap-5') }}
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.students.create') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus text-primary me-2"></i>
                        Add New Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student ID</label>
                            <input type="text" name="student_id_no" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program</label>
                            <input type="text" name="program" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">School/University</label>
                            <input type="text" name="school_university" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_no" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Required Hours</label>
                            <input type="number" name="required_hours" class="form-control" value="500" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-select" required>
                                <option value="">Select Location</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
