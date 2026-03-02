@extends('layouts.guard')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <h1 class="page-title">Guard QR Code Station</h1>
        <p class="page-subtitle">Students scan this QR code to clock in or out</p>

        <div class="card">
            <div class="card-body text-center py-5">
                <div class="qr-container mb-4">
                    <canvas id="qrcode"></canvas>
                </div>

                <div class="timer-display mb-3" id="timer">
                    Refreshing in 30s
                </div>

                <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                    <span class="badge badge-success fs-6 px-4 py-2">
                        <i class="bi bi-check-circle-fill me-2"></i> Active Session
                    </span>
                </div>

                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Students should scan with their mobile app to record attendance
                </p>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="info-card">
                    <h6><i class="bi bi-clock-history me-1"></i> Auto Refresh</h6>
                    <h3>30s</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <h6><i class="bi bi-geo-alt me-1"></i> Location</h6>
                    <h3>{{ auth()->user()->location ? auth()->user()->location->name : 'Default' }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <h6><i class="bi bi-shield-check me-1"></i> Status</h6>
                    <h3 class="text-success">Active</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let countdown = 30;
    let qrData = null;

    async function generateQR(data) {
        const canvas = document.getElementById('qrcode');

        try {
            await QRCode.toCanvas(canvas, data, {
                width: 280,
                margin: 2,
                color: {
                    dark: '#1e293b',
                    light: '#ffffff'
                }
            });
        } catch (err) {
            console.error('Error generating QR:', err);
        }
    }

    function refreshQR() {
        fetch('/guard/qr/refresh')
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    qrData = JSON.stringify({
                        token: data.token,
                        location_id: data.location_id,
                        timestamp: Date.now()
                    });
                    generateQR(qrData);
                    countdown = 30;
                    updateTimer();
                } else if (data.error) {
                    console.error('API Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error refreshing QR:', error);
            });
    }

    function updateTimer() {
        const timerEl = document.getElementById('timer');
        timerEl.textContent = 'Refreshing in ' + countdown + 's';

        timerEl.classList.remove('timer-warning', 'timer-danger');

        if (countdown <= 5) {
            timerEl.classList.add('timer-danger');
        } else if (countdown <= 10) {
            timerEl.classList.add('timer-warning');
        }

        countdown--;

        if (countdown >= 0) {
            setTimeout(updateTimer, 1000);
        } else {
            refreshQR();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(refreshQR, 500);
    });
</script>
@endsection
