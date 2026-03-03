@extends('layouts.app')

@section('title', 'Locations')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container {
            z-index: 1;
        }

        #map,
        [id^="locationMap"],
        #modalMap {
            background: #e8f4f8;
        }
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
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Location Map Overview</h6>
                    <span class="badge bg-primary">{{ $activeLocations->count() }} Active Location(s)</span>
                </div>
            </div>
            <div class="card-body">
                @if($activeLocations->count() > 0)
                    <div id="map"
                        style="height: 450px; width: 100%; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
                    <div class="d-flex gap-3 mt-3 flex-wrap">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2" style="width: 20px; height: 20px; border-radius: 50%;"></span>
                            <small class="text-muted">Active Location (Green circle = radius)</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2" style="width: 20px; height: 20px; border-radius: 50%;"></span>
                            <small class="text-muted">Inactive Location</small>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-geo-alt" style="font-size: 4rem; color: var(--gray-300);"></i>
                        <p class="text-muted mt-3">No active locations with coordinates configured</p>
                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                            <i class="bi bi-plus-lg me-1"></i>Add Your First Location
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            @foreach($locations as $location)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $location->name }}</h5>
                            <span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="card-body">
                            @if($location->description)
                                <p class="text-muted mb-3">{{ $location->description }}</p>
                            @endif

                            @if($location->latitude && $location->longitude)
                                <div class="location-map-container mb-3">
                                    <div id="locationMap{{ $loop->iteration }}"
                                        style="height: 200px; width: 100%; border-radius: 10px; border: 2px solid #e2e8f0;"></div>
                                </div>
                            @endif

                            <div class="location-details mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i>Coordinates:</span>
                                    <code
                                        class="bg-light px-2 py-1 rounded">{{ $location->latitude && $location->longitude ? number_format($location->latitude, 6) . ', ' . number_format($location->longitude, 6) : 'Not set' }}</code>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted"><i class="bi bi-circle-fill me-1"
                                            style="font-size: 8px; vertical-align: middle;"></i>Radius:</span>
                                    <span class="badge bg-info text-dark"
                                        style="font-size: 1rem;">{{ $location->radius_meters }} meters</span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted d-block mb-1">Secret Key:</small>
                                <code class="d-block text-truncate p-2 bg-light rounded"
                                    style="font-size: 9px; word-break: break-all; max-height: 60px; overflow-y: auto;">{{ $location->secret_key }}</code>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#editLocationModal{{ $loop->iteration }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <form action="{{ route('admin.locations.delete', $location->id) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to delete this location?');">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                            <form action="{{ route('admin.locations.regenerate-key', $location->id) }}" method="POST"
                                style="display: inline;">
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
                                <textarea name="description" class="form-control"
                                    rows="2">{{ $location->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" name="latitude" class="form-control"
                                    value="{{ $location->latitude }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" name="longitude" class="form-control"
                                    value="{{ $location->longitude }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Radius (meters)</label>
                                <input type="number" name="radius_meters" class="form-control"
                                    value="{{ $location->radius_meters }}" min="10" max="1000" required>
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
        <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.locations.create') }}" id="addLocationForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-geo-alt-fill me-2 text-success"></i>Add New Location</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-4">
                                {{-- LEFT: Map --}}
                                <div class="col-lg-7">
                                    <label class="form-label fw-semibold">Pick Location on Map</label>
                                    <div id="modalMap" style="height: 320px; width: 100%; border-radius: 10px; border: 2px solid #e2e8f0;"></div>
                                    <div class="d-flex gap-2 mt-2 flex-wrap align-items-center">
                                        <button type="button" id="useMyLocationBtn" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-crosshair me-1"></i>Use My Location
                                        </button>
                                        <span id="coordsBadge" class="badge bg-light text-dark border" style="font-size:0.8rem; display:none;">
                                            <i class="bi bi-geo-alt me-1 text-success"></i><span id="coordsText"></span>
                                        </span>
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Click the map to pin a location</small>
                                    </div>
                                </div>
                                {{-- RIGHT: Form Fields --}}
                                <div class="col-lg-5">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Location Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required placeholder="e.g., Main Gate">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description"></textarea>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Latitude</label>
                                            <input type="number" step="any" name="latitude" class="form-control" id="addLatitude" placeholder="e.g., 14.5995">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Longitude</label>
                                            <input type="number" step="any" name="longitude" class="form-control" id="addLongitude" placeholder="e.g., 120.9842">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Radius <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="radius_meters" class="form-control" id="addRadius" value="100" min="10" max="5000" required>
                                            <span class="input-group-text">meters</span>
                                        </div>
                                        <div class="form-text">Min: 10 m &nbsp;|&nbsp; Max: 5,000 m</div>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="addIsActive" value="1" checked>
                                        <label class="form-check-label" for="addIsActive">Active (visible to students)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-lg me-1"></i>Create Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@php
    // Prepare data for JavaScript - re-map from paginated $locations collection
    $jsActiveLocations = $locations->filter(function ($l) {
        return $l->latitude && $l->longitude && $l->is_active;
    })->map(function ($l) {
        return [
            'name' => $l->name,
            'lat' => (float) $l->latitude,
            'lng' => (float) $l->longitude,
            'radius' => (int) $l->radius_meters,
        ];
    })->values();

    $allLocationsData = $locations->map(function ($l, $index) {
        return [
            'index' => $index + 1,
            'lat' => $l->latitude ? (float) $l->latitude : null,
            'lng' => $l->longitude ? (float) $l->longitude : null,
            'radius' => (int) $l->radius_meters,
            'isActive' => (bool) $l->is_active,
        ];
    })->values();
@endphp

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            'use strict';

            var mainMap = null;
            var modalMap = null;
            var modalMarker = null;

            // PHP data passed to JavaScript (use $jsActiveLocations to avoid variable conflict)
            var locationsData = {!! json_encode($jsActiveLocations) !!};
            var allLocationsData = {!! json_encode($allLocationsData) !!};

            // Default center - Philippines capital; overridden if locations exist
            var defaultLat = 14.5995;
            var defaultLng = 120.9842;

            function initMaps() {
                if (locationsData.length > 0) {
                    defaultLat = locationsData[0].lat;
                    defaultLng = locationsData[0].lng;
                }

                // ── Main overview map (only shown when active locations exist) ──
                var mainMapEl = document.getElementById('map');
                if (mainMapEl) {
                    mainMap = L.map('map', { scrollWheelZoom: false }).setView([defaultLat, defaultLng], locationsData.length > 1 ? 12 : 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(mainMap);

                    var bounds = [];
                    for (var i = 0; i < locationsData.length; i++) {
                        var loc = locationsData[i];
                        var marker = L.marker([loc.lat, loc.lng]).addTo(mainMap);
                        marker.bindPopup(
                            '<div class="text-center"><strong>' + escapeHtml(loc.name) +
                            '</strong><br><span class="badge bg-success">Radius: ' + loc.radius + ' m</span></div>'
                        );
                        L.circle([loc.lat, loc.lng], {
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.2,
                            weight: 2,
                            radius: loc.radius
                        }).addTo(mainMap);
                        bounds.push([loc.lat, loc.lng]);
                    }
                    if (bounds.length > 0) {
                        mainMap.fitBounds(bounds, { padding: [50, 50] });
                    }
                }

                // ── Mini maps for every location card ──
                for (var j = 0; j < allLocationsData.length; j++) {
                    (function (locData) {
                        if (!locData.lat || !locData.lng) return;

                        var mapEl = document.getElementById('locationMap' + locData.index);
                        if (!mapEl) return;

                        var miniMap = L.map('locationMap' + locData.index, {
                            zoomControl: true,
                            scrollWheelZoom: false,
                            attributionControl: false
                        }).setView([locData.lat, locData.lng], 16);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19
                        }).addTo(miniMap);

                        // Find name from active locations data
                        var nameEntry = locationsData.find(function (l) {
                            return l.lat === locData.lat && l.lng === locData.lng;
                        });
                        var locName = nameEntry ? nameEntry.name : 'Location';

                        var miniMarker = L.marker([locData.lat, locData.lng]).addTo(miniMap);
                        miniMarker.bindPopup('<strong>' + escapeHtml(locName) + '</strong>');

                        L.circle([locData.lat, locData.lng], {
                            color: locData.isActive ? '#10b981' : '#9ca3af',
                            fillColor: locData.isActive ? '#10b981' : '#9ca3af',
                            fillOpacity: 0.15,
                            weight: 2,
                            radius: locData.radius
                        }).addTo(miniMap);

                        // Zoom to show the whole radius
                        var circleBounds = L.circle([locData.lat, locData.lng], { radius: locData.radius }).getBounds();
                        miniMap.fitBounds(circleBounds, { padding: [20, 20] });

                    })(allLocationsData[j]);
                }

                // ── Add Location modal map ──
                var addModal        = document.getElementById('addLocationModal');
                var modalRadiusCircle = null;

                function placeModalPin(lat, lng) {
                    lat = parseFloat(lat);
                    lng = parseFloat(lng);
                    if (isNaN(lat) || isNaN(lng)) return;

                    // Update inputs
                    var latInput = document.getElementById('addLatitude');
                    var lngInput = document.getElementById('addLongitude');
                    if (latInput) latInput.value = lat.toFixed(6);
                    if (lngInput) lngInput.value = lng.toFixed(6);

                    // Update marker
                    if (modalMarker) modalMap.removeLayer(modalMarker);
                    modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);

                    // Drag marker → update inputs + circle
                    modalMarker.on('dragend', function(ev) {
                        var pos = ev.target.getLatLng();
                        placeModalPin(pos.lat, pos.lng);
                    });

                    // Update radius circle
                    updateRadiusCircle(lat, lng);

                    // Show coords badge
                    var badge = document.getElementById('coordsBadge');
                    var text  = document.getElementById('coordsText');
                    if (badge && text) {
                        text.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
                        badge.style.display = '';
                    }
                }

                function updateRadiusCircle(lat, lng) {
                    if (!modalMap) return;
                    if (lat === undefined) {
                        var latInput = document.getElementById('addLatitude');
                        var lngInput = document.getElementById('addLongitude');
                        if (!latInput || !latInput.value) return;
                        lat = parseFloat(latInput.value);
                        lng = parseFloat(lngInput.value);
                    }
                    if (isNaN(lat) || isNaN(lng)) return;

                    var radiusInput = document.getElementById('addRadius');
                    var radius = radiusInput ? parseInt(radiusInput.value) || 100 : 100;

                    if (modalRadiusCircle) modalMap.removeLayer(modalRadiusCircle);
                    modalRadiusCircle = L.circle([lat, lng], {
                        color: '#10b981',
                        fillColor: '#10b981',
                        fillOpacity: 0.15,
                        weight: 2,
                        radius: radius
                    }).addTo(modalMap);

                    // Zoom to fit the circle
                    modalMap.fitBounds(modalRadiusCircle.getBounds(), { padding: [30, 30] });
                }

                if (addModal) {
                    // Init map when modal opens
                    addModal.addEventListener('shown.bs.modal', function () {
                        setTimeout(function () {
                            if (modalMap) {
                                modalMap.invalidateSize();
                                return;
                            }
                            var modalMapEl = document.getElementById('modalMap');
                            if (!modalMapEl) return;

                            modalMap = L.map('modalMap', { scrollWheelZoom: true })
                                        .setView([defaultLat, defaultLng], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                                maxZoom: 19
                            }).addTo(modalMap);

                            // Click map → place pin
                            modalMap.on('click', function (e) {
                                placeModalPin(e.latlng.lat, e.latlng.lng);
                            });

                            // Radius input → update circle live
                            var radiusInput = document.getElementById('addRadius');
                            if (radiusInput) {
                                radiusInput.addEventListener('input', function() {
                                    updateRadiusCircle();
                                });
                            }

                            // Lat/Lng manual input → update marker + circle
                            var latInput = document.getElementById('addLatitude');
                            var lngInput = document.getElementById('addLongitude');
                            function onManualCoordChange() {
                                var lat = parseFloat(latInput.value);
                                var lng = parseFloat(lngInput.value);
                                if (!isNaN(lat) && !isNaN(lng)) {
                                    if (modalMarker) modalMap.removeLayer(modalMarker);
                                    modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);
                                    modalMarker.on('dragend', function(ev) {
                                        var pos = ev.target.getLatLng();
                                        placeModalPin(pos.lat, pos.lng);
                                    });
                                    updateRadiusCircle(lat, lng);
                                }
                            }
                            if (latInput) latInput.addEventListener('change', onManualCoordChange);
                            if (lngInput) lngInput.addEventListener('change', onManualCoordChange);

                        }, 350);
                    });

                    // GPS — Use My Location
                    var gpsBtn = document.getElementById('useMyLocationBtn');
                    if (gpsBtn) {
                        gpsBtn.addEventListener('click', function() {
                            if (!navigator.geolocation) {
                                alert('Geolocation is not supported by your browser.');
                                return;
                            }
                            gpsBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Locating...';
                            gpsBtn.disabled = true;
                            navigator.geolocation.getCurrentPosition(
                                function(pos) {
                                    gpsBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i>Use My Location';
                                    gpsBtn.disabled = false;
                                    if (modalMap) {
                                        modalMap.setView([pos.coords.latitude, pos.coords.longitude], 16);
                                        placeModalPin(pos.coords.latitude, pos.coords.longitude);
                                    }
                                },
                                function() {
                                    gpsBtn.innerHTML = '<i class="bi bi-crosshair me-1"></i>Use My Location';
                                    gpsBtn.disabled = false;
                                    alert('Unable to get your location. Please allow location access and try again.');
                                },
                                { enableHighAccuracy: true, timeout: 10000 }
                            );
                        });
                    }

                    // Full reset when modal closes
                    addModal.addEventListener('hidden.bs.modal', function () {
                        if (modalMap) {
                            modalMap.remove();
                            modalMap = null;
                        }
                        modalMarker = null;
                        modalRadiusCircle = null;

                        // Reset form fields
                        var form = document.getElementById('addLocationForm');
                        if (form) form.reset();

                        // Reset radius default
                        var radiusInput = document.getElementById('addRadius');
                        if (radiusInput) radiusInput.value = 100;

                        // Hide coords badge
                        var badge = document.getElementById('coordsBadge');
                        if (badge) badge.style.display = 'none';
                    });
                }
            }

            function escapeHtml(str) {
                var d = document.createElement('div');
                d.textContent = str || '';
                return d.innerHTML;
            }

            // Run after DOM + Leaflet are ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMaps);
            } else {
                initMaps();
            }

        })();
    </script>
@endpush