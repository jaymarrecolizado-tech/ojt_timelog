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
                Showing active scan locations. Click on a marker to see details.
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
                                <td>{{ $location->radius_meters }} meters</td>
                            </tr>
                        </table>

                        <div class="mt-3">
                            <small class="text-muted">Secret Key:</small>
                            <code class="d-block text-truncate p-2 bg-light rounded">{{ $location->secret_key }}</code>
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

<!-- Add Location Modal -->
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
                        <input type="number" name="radius_meters" class="form-control" value="100" min="10" max="1000">
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
    @php
        $locationArray = [];
        foreach($locations as $index => $loc) {
            $locationArray[] = [
                'index' => $index,
                'name' => $loc->name,
                'lat' => $loc->latitude ? floatval($loc->latitude) : null,
                'lng' => $loc->longitude ? floatval($loc->longitude) : null,
                'radius' => $loc->radius_meters,
                'active' => $loc->is_active,
                'hasCoords' => !empty($loc->latitude) && !empty($loc->longitude)
            ];
        }
    @endphp

    const allLocations = {{ json_encode($locationArray) }};
    const mappableLocations = allLocations.filter(loc => loc.hasCoords);
    const defaultLat = mappableLocations.length > 0 ? mappableLocations[0].lat : 17.8;
    const defaultLng = mappableLocations.length > 0 ? mappableLocations[0].lng : 121.8;

    // Main map
    if (document.getElementById('map') && typeof L !== 'undefined') {
        const mainMap = L.map('map').setView([defaultLat, defaultLng], mappableLocations.length > 0 ? 13 : 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mainMap);

        mappableLocations.forEach(loc => {
            L.marker([loc.lat, loc.lng]).addTo(mainMap)
                .bindPopup('<strong>' + loc.name + '</strong><br>Radius: ' + loc.radius + 'm');

            L.circle([loc.lat, loc.lng], {
                color: loc.active ? '#10b981' : '#9ca3af',
                fillColor: loc.active ? '#10b981' : '#9ca3af',
                fillOpacity: 0.15,
                radius: loc.radius
            }).addTo(mainMap);
        });

        if (mappableLocations.length > 1) {
            const group = L.featureGroup(mappableLocations.map(loc => L.marker([loc.lat, loc.lng])));
            mainMap.fitBounds(group.getBounds().pad(0.1));
        }
    }

    // Mini maps for cards
    allLocations.forEach(loc => {
        if (!loc.hasCoords) return;
        const mapEl = document.getElementById('locationMap' + loc.index);
        if (mapEl && typeof L !== 'undefined') {
            L.map(mapEl, { zoomControl: false, scrollWheelZoom: false })
                .setView([loc.lat, loc.lng], 15)
                .addLayer(L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'))
                .addLayer(L.marker([loc.lat, loc.lng]));
        }
    });

    // Modal map
    let modalMap = null;
    let modalMarker = null;

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
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                document.getElementById('latitudeInput').value = lat;
                document.getElementById('longitudeInput').value = lng;
                document.getElementById('selectedCoords').textContent = lat + ', ' + lng;

                if (modalMarker) modalMap.removeLayer(modalMarker);
                modalMarker = L.marker([lat, lng]).addTo(modalMap);
            });
        }, 300);
    });
});
</script>
@endpush
