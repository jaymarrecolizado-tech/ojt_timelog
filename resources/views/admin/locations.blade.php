@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Scan Locations</h2>
        </div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                <i class="bi bi-plus-lg me-2"></i>Add Location
            </button>
        </div>
    </div>

    <div class="row g-4">
        @forelse($locations as $location)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $location->name }}</h5>
                        <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if($location->description)
                            <p class="text-muted">{{ $location->description }}</p>
                        @endif
                        
                        <table class="table table-sm table-borderless">
                            @if($location->latitude && $location->longitude)
                                <tr>
                                    <td class="text-muted">Coordinates:</td>
                                    <td>{{ $location->latitude }}, {{ $location->longitude }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Radius:</td>
                                <td>{{ $location->radius_meters }} meters</td>
                            </tr>
                        </table>

                        <div class="mt-3">
                            <small class="text-muted">Secret Key:</small>
                            <code class="d-block text-truncate">{{ $location->secret_key }}</code>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                        <button class="btn btn-sm btn-outline-secondary">Regenerate Key</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    No locations configured yet. Add your first scan location.
                </div>
            </div>
        @endforelse
    </div>
    @if($locations->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <nav>
                {{ $locations->links('pagination.bootstrap-5') }}
            </nav>
        </div>
    @endif
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.locations.create') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" name="radius_meters" class="form-control" value="100">
                        <div class="form-text">Maximum distance allowed from this location</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
