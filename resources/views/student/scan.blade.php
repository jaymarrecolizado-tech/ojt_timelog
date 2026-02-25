@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('styles')
<style>
    #qr-video {
        width: 100%;
        max-width: 400px;
        border-radius: 8px;
    }
    .scan-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 200px;
        border: 2px solid #fff;
        border-radius: 8px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
    }
    .scan-line {
        position: absolute;
        width: 100%;
        height: 2px;
        background: #10b981;
        animation: scan 2s linear infinite;
    }
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h2 class="mb-0">Scan QR Code</h2>
                <div style="width: 80px;"></div>
            </div>
            
            @if($nextType)
                <div class="alert alert-info mb-4">
                    <h5 class="mb-0">Next: {{ $nextType['label'] }}</h5>
                </div>

                @if($locations->count() > 0)
                    <div class="mb-4">
                        <label for="location-select" class="form-label">Select Location:</label>
                        <select id="location-select" class="form-select">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="alert alert-warning mb-4">
                        No active locations available. Please contact the administrator.
                    </div>
                @endif

                <div class="position-relative d-inline-block mb-4">
                    <video id="qr-video" autoplay playsinline></video>
                    <div class="scan-overlay">
                        <div class="scan-line"></div>
                    </div>
                </div>

                <div id="scan-status" class="alert alert-secondary">
                    Position QR code within the frame
                </div>

                <div class="d-grid gap-2">
                    <button id="start-scan" class="btn btn-primary btn-lg">
                        <i class="bi bi-camera me-2"></i>Start Camera
                    </button>
                    <button id="stop-scan" class="btn btn-outline-secondary" style="display: none;">
                        Stop Camera
                    </button>
                </div>

                <!-- Manual QR Entry for Mobile/Tablet -->
                <hr class="my-4">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-keyboard me-2"></i>Manual Entry
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">If camera doesn't work, manually enter the QR code:</p>
                        <div class="input-group">
                            <input type="text" id="manual-qr-code" class="form-control" placeholder="Enter QR code here">
                            <button class="btn btn-success" type="button" id="submit-manual-qr">
                                <i class="bi bi-check-lg"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-success">
                    <h5 class="mb-0">All scans completed for today!</h5>
                    <p class="mb-0 mt-2">You've already scanned 4 times today.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrCode;
    const videoElement = document.getElementById('qr-video');
    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');
    const statusDiv = document.getElementById('scan-status');

    startBtn.addEventListener('click', async () => {
        // Check if location is selected
        const locationSelect = document.getElementById('location-select');
        if (locationSelect && !locationSelect.value) {
            statusDiv.className = 'alert alert-warning';
            statusDiv.textContent = 'Please select a location before scanning.';
            return;
        }
        
        try {
            html5QrCode = new Html5Qrcode("qr-video");
            
            await html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 200, height: 200 }
                },
                onScanSuccess,
                onScanFailure
            );

            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            statusDiv.className = 'alert alert-info';
            statusDiv.textContent = 'Camera active - Scanning for QR code...';
        } catch (err) {
            console.error('Error starting camera:', err);
            statusDiv.className = 'alert alert-danger';
            statusDiv.textContent = 'Error accessing camera. Please allow camera permissions.';
        }
    });

    stopBtn.addEventListener('click', async () => {
        if (html5QrCode) {
            await html5QrCode.stop();
            html5QrCode = null;
        }
        startBtn.style.display = 'block';
        stopBtn.style.display = 'none';
        statusDiv.className = 'alert alert-secondary';
        statusDiv.textContent = 'Camera stopped';
    });

    async function onScanSuccess(decodedText) {
        // Get selected location
        const locationSelect = document.getElementById('location-select');
        const locationId = locationSelect ? locationSelect.value : null;
        
        if (!locationId) {
            statusDiv.className = 'alert alert-warning';
            statusDiv.textContent = 'Please select a location first.';
            return;
        }
        
        // Stop scanning temporarily
        if (html5QrCode) {
            await html5QrCode.pause();
        }

        statusDiv.className = 'alert alert-warning';
        statusDiv.textContent = 'Processing scan...';

        try {
            const response = await fetch('{{ route('qr.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    token: decodedText,
                    student_id: '{{ auth()->user()->student->id }}',
                    location_id: locationId
                })
            });

            const data = await response.json();

            if (data.success) {
                statusDiv.className = 'alert alert-success';
                statusDiv.innerHTML = `<strong>Success!</strong> ${data.message}`;
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '{{ route('student.dashboard') }}';
                }, 2000);
            } else {
                statusDiv.className = 'alert alert-danger';
                statusDiv.textContent = data.error || 'Scan failed. Please try again.';
                
                // Resume scanning
                if (html5QrCode) {
                    await html5QrCode.resume();
                }
            }
        } catch (error) {
            console.error('Error:', error);
            statusDiv.className = 'alert alert-danger';
            statusDiv.textContent = 'Network error. Please try again.';
            
            if (html5QrCode) {
                await html5QrCode.resume();
            }
        }
    }

    function onScanFailure(error) {
        // Ignore scan failures (happens frequently when no QR is in frame)
    }

    // Manual QR Code Entry
    document.getElementById('submit-manual-qr').addEventListener('click', async function() {
        const manualInput = document.getElementById('manual-qr-code');
        const qrCode = manualInput.value.trim();
        
        if (!qrCode) {
            statusDiv.className = 'alert alert-warning';
            statusDiv.textContent = 'Please enter a QR code.';
            return;
        }
        
        await onScanSuccess(qrCode);
        manualInput.value = ''; // Clear input after submission
    });

    // Allow Enter key to submit
    document.getElementById('manual-qr-code').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('submit-manual-qr').click();
        }
    });
</script>
@endsection
