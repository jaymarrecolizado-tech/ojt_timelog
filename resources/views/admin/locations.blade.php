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

    <div class="card mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Location Map</h6>
        </div>
        <div class="card-body">
            <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
            <small class="text-muted mt-2 d-block">
                <i class="bi bi-info-circle me-1"></i>Showing active scan locations with radius coverage.
            </small>
        </div>
    </div>

    <div class="row g-4">
        @foreach($locations as $location)
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
                        <p class="text-muted mb-2">{{ $location->description }}</p>
                    @endif

                    @if($location->latitude && $location->longitude)
                        <div id="locationMap{{ $loop->iteration }}" style="height: 150px; width: 100%; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 10px;"></div>
                    @endif

                    <table class="table table-sm table-borderless">
                        @if($location->latitude && $location->longitude)
                            <tr>
                                <td class="text-muted">Coordinates:</td>
                                <td><code>{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}</code></td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Radius:</td>
                            <td><span class="badge bg-info text-dark">{{ $location->radius_meters }}m</span></td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <small class="text-muted">Secret Key:</small>
                        <code class="d-block text-truncate p-2 bg-light rounded" style="font-size: 9px; word-break: break-all;">{{ $location->secret_key }}</code>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $loop->iteration }}">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <form action="{{ route('admin.locations.delete', $location->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this location?');">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </form>
                    <form action="{{ route('admin.locations.regenerate-key', $location->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary ms-1">
                            <i class="bi bi-key me-1"></i>New Key
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if($locations->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $locations->links('pagination.bootstrap-5') }}
        </div>
    @endif
</div>

@foreach($locations as $location)
<div class="modal fade" id="editLocationModal{{ $loop->iteration }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.locations.update', $location->id) }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit: {{ $location->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $location->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $location->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" class="form-control" value="{{ $location->latitude }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" class="form-control" value="{{ $location->longitude }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" name="radius_meters" class="form-control" value="{{ $location->radius_meters }}" min="10" max="1000" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $location->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Add Location Modal --}}
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Main Gate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" class="form-control" placeholder="e.g., 17.8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" class="form-control" placeholder="e.g., 121.8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" name="radius_meters" class="form-control" value="100" min="10" max="1000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var mainMap = null;
var modalMap = null;
var modalMarker = null;

window.addEventListener('load', function() {
    @php
        $activeLocations = $locations->filter(function($l) { return $l->latitude && $l->longitude && $l->is_active; });
    @endphp

    var locations = [
        @foreach($activeLocations as $l)
        { name: "{{ $l->name }}", lat: {{ number_format($l->latitude, 6) }}, lng: {{ number_format($l->longitude, 6) }}, radius: {{ $l->radius_meters }} }@if($loop->last)@else, @endif
        @endforeach
    ];

    var defaultLat = locations.length ? locations[0].lat : 17.8;
    var defaultLng = locations.length ? locations[0].lng : 121.8;

    // Main map
    if (document.getElementById('map')) {
        mainMap = L.map('map').setView([defaultLat, defaultLng], locations.length ? 13 : 9);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(mainMap);

        for (var i = 0; i < locations.length; i++) {
            var loc = locations[i];
            L.marker([loc.lat, loc.lng]).addTo(mainMap).bindPopup('<strong>' + loc.name + '</strong><br>Radius: ' + loc.radius + 'm');
            L.circle([loc.lat, loc.lng], { color: '#10b981', fillColor: '#10b981', fillOpacity: 0.15, radius: loc.radius }).addTo(mainMap);
        }
    }

    // Mini maps for all locations
    @foreach($locations as $location)
    @if($location->latitude && $location->longitude)
    (function() {
        var map = L.map('locationMap{{ $loop->iteration }}', { zoomControl: false, scrollWheelZoom: false })
            .setView([{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '' }).addTo(map);
        L.marker([{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}]).addTo(map);
        L.circle([{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}], {
            color: {{ $location->is_active ? "'#10b981'" : "'#9ca3af'" }},
            fillColor: {{ $location->is_active ? "'#10b981'" : "'#9ca3af'" }},
            fillOpacity: 0.2,
            radius: {{ $location->radius_meters }}
        }).addTo(map);
    })();
    @endif
    @endforeach

    // Add location modal map
    document.getElementById('addLocationModal').addEventListener('shown.bs.modal', function() {
        setTimeout(function() {
            if (modalMap) { modalMap.invalidateSize(); return; }
            modalMap = L.map('modalMap').setView([defaultLat, defaultLng], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(modalMap);
            modalMap.on('click', function(e) {
                var lat = e.latlng.lat.toFixed(6);
                var lng = e.latlng.lng.toFixed(6);
                document.querySelector('#addLocationModal input[name="latitude"]').value = lat;
                document.querySelector('#addLocationModal input[name="longitude"]').value = lng;
                if (modalMarker) modalMap.removeLayer(modalMarker);
                modalMarker = L.marker([lat, lng]).addTo(modalMap);
            });
        }, 300);
    });
});
</script>
@endpush
