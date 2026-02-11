@extends('layouts.guard')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <h2 class="mb-3">Guard QR Code</h2>
        <p class="text-muted mb-4">Students scan this to clock in/out</p>

        <div class="card mb-4">
            <div class="card-body">
                <div class="qr-container mb-4">
                    <canvas id="qrcode"></canvas>
                </div>

                <div class="timer mb-3" id="timer">
                    Refreshing in 30s
                </div>

                <div class="badge bg-success fs-6 mb-3">
                    <i class="bi bi-check-circle"></i> Active Session
                </div>

                <p class="text-muted">
                    Scan with student app to record attendance
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let countdown = 30;
    let qrData = null;
    
    console.log('Guard QR page loaded');

    async function generateQR(data) {
        console.log('Generating QR with data:', data);
        const canvas = document.getElementById('qrcode');
        
        try {
            await QRCode.toCanvas(canvas, data, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff'
                }
            });
            console.log('QR Code generated successfully');
        } catch (err) {
            console.error('Error generating QR:', err);
        }
    }

    function refreshQR() {
        console.log('Refreshing QR...');
        fetch('/guard/qr/refresh')
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('QR Refresh data:', data);
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
        console.log('DOM loaded, starting QR refresh');
        setTimeout(refreshQR, 500);
    });
</script>
@endsection
