@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>System Settings</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update', 'all') }}">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="mb-3 text-muted">QR Code Settings</h6>
                        <div class="mb-3">
                            <label class="form-label">QR Rotation Interval (seconds)</label>
                            <input type="number" name="qr_rotation_seconds" class="form-control" 
                                   value="{{ $settings->firstWhere('setting_key', 'qr_rotation_seconds')?->setting_value ?? 30 }}">
                            <div class="form-text">How often the QR code refreshes</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Scans Per Day</label>
                            <input type="number" name="max_scans_per_day" class="form-control" 
                                   value="{{ $settings->firstWhere('setting_key', 'max_scans_per_day')?->setting_value ?? 4 }}">
                            <div class="form-text">Maximum number of scans allowed per student per day</div>
                        </div>

                        <hr class="my-4">
                        
                        <h6 class="mb-3 text-muted">Schedule Settings</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">AM Start Time</label>
                                <input type="time" name="schedule_am_start" class="form-control" 
                                       value="{{ $settings->firstWhere('setting_key', 'schedule_am_start')?->setting_value ?? '08:00' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">AM End Time</label>
                                <input type="time" name="schedule_am_end" class="form-control" 
                                       value="{{ $settings->firstWhere('setting_key', 'schedule_am_end')?->setting_value ?? '12:00' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">PM Start Time</label>
                                <input type="time" name="schedule_pm_start" class="form-control" 
                                       value="{{ $settings->firstWhere('setting_key', 'schedule_pm_start')?->setting_value ?? '13:00' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PM End Time</label>
                                <input type="time" name="schedule_pm_end" class="form-control" 
                                       value="{{ $settings->firstWhere('setting_key', 'schedule_pm_end')?->setting_value ?? '17:00' }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Grace Period (minutes)</label>
                            <input type="number" name="grace_period_minutes" class="form-control" 
                                   value="{{ $settings->firstWhere('setting_key', 'grace_period_minutes')?->setting_value ?? 15 }}">
                            <div class="form-text">Minutes after schedule start before marking as late</div>
                        </div>

                        <hr class="my-4">
                        
                        <h6 class="mb-3 text-muted">Geolocation Settings</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="geolocation_required" class="form-check-input" id="geoRequired"
                                       {{ ($settings->firstWhere('setting_key', 'geolocation_required')?->setting_value ?? 'false') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="geoRequired">Require GPS validation</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Distance (meters)</label>
                            <input type="number" name="geolocation_max_distance" class="form-control" 
                                   value="{{ $settings->firstWhere('setting_key', 'geolocation_max_distance')?->setting_value ?? 200 }}">
                            <div class="form-text">Maximum allowed distance from scan location</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">System Info</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Version</td>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Laravel</td>
                            <td>10.x</td>
                        </tr>
                        <tr>
                            <td class="text-muted">PHP</td>
                            <td>{{ phpversion() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
