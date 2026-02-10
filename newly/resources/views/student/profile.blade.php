@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>My Profile</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Student ID</label>
                            <p class="fw-bold">{{ $student->student_id_no }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Full Name</label>
                            <p class="fw-bold">{{ $student->full_name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Department</label>
                            <p class="fw-bold">{{ $student->department }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Program</label>
                            <p class="fw-bold">{{ $student->program }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Email</label>
                            <p class="fw-bold">{{ $student->user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Contact Number</label>
                            <p class="fw-bold">{{ $student->contact_no ?? 'Not set' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">OJT Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Company</label>
                            <p class="fw-bold">{{ $student->company ?? 'Not set' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Supervisor</label>
                            <p class="fw-bold">{{ $student->supervisor_name ?? 'Not set' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">OJT Start Date</label>
                            <p class="fw-bold">{{ $student->ojt_start ? $student->ojt_start->format('F d, Y') : 'Not set' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">OJT End Date</label>
                            <p class="fw-bold">{{ $student->ojt_end ? $student->ojt_end->format('F d, Y') : 'Not set' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted">Required Hours</label>
                            <p class="fw-bold">{{ $student->required_hours }} hours</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Status</label>
                            <p class="fw-bold">
                                <span class="badge bg-{{ $student->status == 'active' ? 'success' : ($student->status == 'completed' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Account</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">Change Password</a>
                        <a href="{{ route('student.logs') }}" class="btn btn-outline-secondary">View Logs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
