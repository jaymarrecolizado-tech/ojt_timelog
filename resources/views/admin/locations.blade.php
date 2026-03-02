@extends('layouts.app')

@section('title', 'Locations')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container { background: #f8f9fa; }
</style>
@endsection

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
                <i class="bi bi-info-circle me-1"></i>
                Showing active scan locations with their radius coverage. Click on a marker to see details.
            </small>
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
                            <p class="text-muted mb-2">{{ $location->description }}</p>
                        @endif

                        @if($location->latitude && $location->longitude)
                            <div id="locationMap{{ $loop->index }}" style="height: 150px; width: 100%; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 10px;"></div>
                        @endif

                        <table class="table table-sm table-borderless">
                            @if($location->latitude && $location->longitude)
                                <tr>
                                    <td class="text-muted">Coordinates:</td>
                                    <td>
                                        <span style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 10px; font-size: 12px; font-family: monospace;">
                                            {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Radius:</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $location->radius_meters }}m</span>
                                </td>
                            </tr>
                        </table>

                        <div class="mt-3">
                            <small class="text-muted">Secret Key:</small>
                            <code class="d-block text-truncate p-2 bg-light rounded" style="font-size: 10px;">{{ $location->secret_key }}</code>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $loop->index }}">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="event.preventDefault(); document.getElementById('deleteForm{{ $loop->index }}').submit();">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                        <form id="deleteForm{{ $loop->index }}" action="{{ route('admin.locations.delete', $location->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button class="btn btn-sm btn-outline-secondary ms-1" onclick="event.preventDefault(); document.getElementById('keyForm{{ $loop->index }}').submit();">
                            <i class="bi bi-key me-1"></i>Regenerate Key
                        </button>
                        <form id="keyForm{{ $loop->index }}" action="{{ route('admin.locations.regenerate-key', $location->id) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No locations configured yet. Add your first scan location.
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

{{-- Edit Location Modals --}}
@foreach($locations as $index => $location)
<div class="modal fade" id="editLocationModal{{ $index }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.locations.update', $location->id) }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
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

                    <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px; margin-bottom: 10px;">
                        <i class="bi bi-hand-index-thumb me-2"></i>
                        <strong>Click on the map</strong> to update the location coordinates.
                    </div>
                    <div id="editModalMap{{ $index }}" style="height: 300px; width: 100%; border-radius: 6px; border: 1px solid #cbd5e0; margin-bottom: 10px;"></div>
                    <div class="text-center mb-3">
                        <span id="editSelectedCoords{{ $index }}" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-family: monospace;">
                            {{ $location->latitude && $location->longitude ? number_format($location->latitude, 6) . ', ' . number_format($location->longitude, 6) : 'Click map to select coordinates' }}
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" id="editLatitudeInput{{ $index }}" value="{{ $location->latitude }}" placeholder="e.g., 17.8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" id="editLongitudeInput{{ $index }}" value="{{ $location->longitude }}" placeholder="e.g., 121.8">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" name="radius_meters" class="form-control" value="{{ $location->radius_meters }}" min="10" max="1000" required>
                        <div class="form-text">Maximum distance allowed from this location (10-1000 meters)</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $location->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Location</button>
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
            <form method="POST" action="{{ route('admin.locations.create') }}" id="locationForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Main Office, Building A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this location"></textarea>
                    </div>

                    <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px; margin-bottom: 10px;">
                        <i class="bi bi-hand-index-thumb me-2"></i>
                        <strong>Click on the map</strong> to set the location coordinates, or enter manually below.
                    </div>
                    <div id="modalMap" style="height: 300px; width: 100%; border-radius: 6px; border: 1px solid #cbd5e0; margin-bottom: 10px;"></div>
                    <div class="text-center mb-3">
                        <span id="selectedCoords" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-family: monospace;">
                            Click map to select coordinates
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" id="latitudeInput" placeholder="e.g., 17.8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" id="longitudeInput" placeholder="e.g., 121.8">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Radius (meters)</label>
                        <input type="number" name="radius_meters" class="form-control" value="100" min="10" max="1000" required>
                        <div class="form-text">Maximum distance allowed from this location (10-1000 meters)</div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Location data for main map
    var locationData = [
        @foreach($locations as $index => $loc)
        @if($loc->latitude && $loc->longitude && $loc->is_active)
        {
            index: {{ $index }},
            name: "{{ $loc->name }}",
            lat: {{ number_format($loc->latitude, 8) }},
            lng: {{ number_format($loc->longitude, 8) }},
            radius: {{ $loc->radius_meters }}
        }@if(!$loop->last),@endif
        @endif
        @endforeach
    ];

    var defaultLat = locationData.length > 0 ? locationData[0].lat : 17.8;
    var defaultLng = locationData.length > 0 ? locationData[0].lng : 121.8;

    // Main map
    if (document.getElementById('map')) {
        var mainMap = L.map('map').setView([defaultLat, defaultLng], locationData.length > 0 ? 13 : 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mainMap);

        for (var i = 0; i < locationData.length; i++) {
            var loc = locationData[i];
            L.marker([loc.lat, loc.lng]).addTo(mainMap)
                .bindPopup('<strong>' + loc.name + '</strong><br>Radius: ' + loc.radius + 'm');

            L.circle([loc.lat, loc.lng], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.15,
                radius: loc.radius
            }).addTo(mainMap);
        }
    }

    // Mini maps for cards (all locations with coordinates)
    @foreach($locations as $index => $loc)
    @if($loc->latitude && $loc->longitude)
    (function() {
        var mapEl = document.getElementById('locationMap{{ $loop->index }}');
        if (mapEl) {
            var map = L.map(mapEl, { zoomControl: false, scrollWheelZoom: false })
                .setView([{{ number_format($loc->latitude, 8) }}, {{ number_format($loc->longitude, 8) }}], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: ''
            }).addTo(map);

            L.marker([{{ number_format($loc->latitude, 8) }}, {{ number_format($loc->longitude, 8) }}]).addTo(map);

            L.circle([{{ number_format($loc->latitude, 8) }}, {{ number_format($loc->longitude, 8) }}], {
                color: {{ $loc->is_active ? "'#10b981'" : "'#9ca3af'" }},
                fillColor: {{ $loc->is_active ? "'#10b981'" : "'#9ca3af'" }},
                fillOpacity: 0.2,
                radius: {{ $loc->radius_meters }}
            }).addTo(map);
        }
    })();
    @endif
    @endforeach

    // Add location modal map
    var modalMap = null;
    var modalMarker = null;

    document.getElementById('addLocationModal').addEventListener('shown.bs.modal', function() {
        setTimeout(function() {
            if (modalMap) {
                modalMap.invalidateSize();
                return;
            }

            modalMap = L.map('modalMap').setView([defaultLat, defaultLng], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(modalMap);

            modalMap.on('click', function(e) {
                var lat = e.latlng.lat.toFixed(6);
                var lng = e.latlng.lng.toFixed(6);

                document.getElementById('latitudeInput').value = lat;
                document.getElementById('longitudeInput').value = lng;
                document.getElementById('selectedCoords').textContent = lat + ', ' + lng;

                if (modalMarker) modalMap.removeLayer(modalMarker);
                modalMarker = L.marker([lat, lng]).addTo(modalMap);
            });
        }, 300);
    });

    // Edit location modal maps
    @foreach($locations as $index => $loc)
    (function() {
        var modalId = 'editLocationModal{{ $loop->index }}';
        var mapId = 'editModalMap{{ $loop->index }}';
        var latInputId = 'editLatitudeInput{{ $loop->index }}';
        var lngInputId = 'editLongitudeInput{{ $loop->index }}';
        var coordsId = 'editSelectedCoords{{ $loop->index }}';
        var locLat = {{ number_format($loc->latitude, 8) }};
        var locLng = {{ number_format($loc->longitude, 8) }};

        document.getElementById(modalId).addEventListener('shown.bs.modal', function() {
            setTimeout(function() {
                var map = L.map(mapId).setView([locLat, locLng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                var marker = L.marker([locLat, locLng]).addTo(map);

                L.circle([locLat, locLng], {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.15,
                    radius: {{ $loc->radius_meters }}
                }).addTo(map);

                map.on('click', function(e) {
                    var lat = e.latlng.lat.toFixed(6);
                    var lng = e.latlng.lng.toFixed(6);

                    document.getElementById(latInputId).value = lat;
                    document.getElementById(lngInputId).value = lng;
                    document.getElementById(coordsId).textContent = lat + ', ' + lng;

                    map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                });
            }, 300);
        });
    })();
    @endforeach
});
</script>
@endpush
