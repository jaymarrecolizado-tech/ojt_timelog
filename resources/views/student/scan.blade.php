@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('styles')
<style>
    #qr-video {
        width: 100%;
        max-width: 400px;
        border-radius: 16px;
    }

    .scan-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 16px;
        overflow: hidden;
    }

    .scan-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 220px;
        height: 220px;
        border: 3px solid #fff;
        border-radius: 16px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.6);
    }

    .scan-overlay::before,
    .scan-overlay::after {
        content: '';
        position: absolute;
        width: 40px;
        height: 40px;
        border-color: #10b981;
        border-style: solid;
    }

    .scan-overlay::before {
        top: -2px;
        left: -2px;
        border-width: 4px 0 0 4px;
        border-radius: 12px 0 0 0;
    }

    .scan-overlay::after {
        bottom: -2px;
        right: -2px;
        border-width: 0 4px 4px 0;
        border-radius: 0 0 12px 0;
    }

    .scan-corner-top-right,
    .scan-corner-bottom-left {
        position: absolute;
        width: 40px;
        height: 40px;
        border-color: #10b981;
        border-style: solid;
    }

    .scan-corner-top-right {
        top: -2px;
        right: -2px;
        border-width: 4px 4px 0 0;
        border-radius: 0 12px 0 0;
    }

    .scan-corner-bottom-left {
        bottom: -2px;
        left: -2px;
        border-width: 0 0 4px 4px;
        border-radius: 0 0 0 12px;
    }

    .scan-line {
        position: absolute;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #10b981, transparent);
        animation: scan 2s linear infinite;
    }

    @keyframes scan {
        0% { top: 0; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }

    .status-card {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
    }

    .status-card.info {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(99, 102, 241, 0.12) 100%);
        border: 1px solid rgba(59, 130, 246, 0.2);
        color: #1e40af;
    }

    .status-card.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(251, 191, 36, 0.12) 100%);
        border: 1px solid rgba(245, 158, 11, 0.2);
        color: #92400e;
    }

    .status-card.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(34, 197, 94, 0.12) 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #065f46;
    }

    .status-card.danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.12) 0%, rgba(248, 113, 113, 0.12) 100%);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #991b1b;
    }

    .manual-entry-card {
        background: var(--gray-50);
        border-radius: 12px;
        overflow: hidden;
    }

    .manual-entry-card .card-header {
        background: var(--gray-200);
        border-bottom: 1px solid var(--gray-200);
    }

    .scan-buttons .btn {
        padding: 1rem 2rem;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <div class="text-center flex-grow-1">
                    <h2 class="fw-bold mb-1">Scan QR Code</h2>
                    <p class="text-muted mb-0">Position the QR code within the frame</p>
                </div>
                <div style="width: 100px;"></div>
            </div>

            @if($nextType)
                <!-- Next Action Alert -->
                <div class="status-card info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Next Action Required</h6>
                            <p class="mb-0">{{ $nextType['label'] }}</p>
                        </div>
                    </div>
                </div>

                @if($locations->count() > 0)
                    <!-- Location Selector -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <label for="location-select" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i> Select Location
                            </label>
                            <select id="location-select" class="form-select form-select-lg">
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <div class="status-card warning mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">No Locations Available</h6>
                                <p class="mb-0">Please contact the administrator to set up locations.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Scanner -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="scan-wrapper mb-4">
                            <video id="qr-video" autoplay playsinline></video>
                            <div class="scan-overlay">
                                <div class="scan-corner-top-right"></div>
                                <div class="scan-corner-bottom-left"></div>
                                <div class="scan-line"></div>
                            </div>
                        </div>

                        <div id="scan-status" class="status-card warning">
                            <i class="bi bi-camera me-2"></i>
                            Click "Start Camera" to begin scanning
                        </div>

                        <div class="scan-buttons d-grid gap-2 mt-4">
                            <button id="start-scan" class="btn btn-primary btn-lg">
                                <i class="bi bi-camera-fill me-2"></i>Start Camera
                            </button>
                            <button id="stop-scan" class="btn btn-outline-danger" style="display: none;">
                                <i class="bi bi-stop-circle me-2"></i>Stop Camera
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Manual Entry -->
                <div class="manual-entry-card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-keyboard me-2"></i>Manual Entry
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">If camera doesn't work, enter the QR code manually:</p>
                        <div class="input-group">
                            <input type="text" id="manual-qr-code" class="form-control form-control-lg" placeholder="Enter QR code here">
                            <button class="btn btn-success" type="button" id="submit-manual-qr">
                                <i class="bi bi-check-lg me-1"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- All Scans Complete -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="success-icon mb-4">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h3 class="fw-bold mb-2">All Scans Complete!</h3>
                        <p class="text-muted mb-4">You've already scanned 4 times today.</p>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .success-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .success-icon i {
        font-size: 2.5rem;
        color: white;
    }
</style>
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
        const locationSelect = document.getElementById('location-select');
        if (locationSelect && !locationSelect.value) {
            statusDiv.className = 'status-card warning';
            statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Please select a location before scanning.';
            return;
        }

        try {
            html5QrCode = new Html5Qrcode("qr-video");

            await html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 220, height: 220 }
                },
                onScanSuccess,
                onScanFailure
            );

            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            statusDiv.className = 'status-card info';
            statusDiv.innerHTML = '<i class="bi bi-qr-code-scan me-2"></i>Camera active - Scanning for QR code...';
        } catch (err) {
            console.error('Error starting camera:', err);
            statusDiv.className = 'status-card danger';
            statusDiv.innerHTML = '<i class="bi bi-camera-video-off me-2"></i>Error accessing camera. Please allow camera permissions.';
        }
    });

    stopBtn.addEventListener('click', async () => {
        if (html5QrCode) {
            await html5QrCode.stop();
            html5QrCode = null;
        }
        startBtn.style.display = 'block';
        stopBtn.style.display = 'none';
        statusDiv.className = 'status-card warning';
        statusDiv.innerHTML = '<i class="bi bi-camera me-2"></i>Camera stopped. Click "Start Camera" to begin again.';
    });

    async function onScanSuccess(decodedText) {
        let qrToken = decodedText;
        let qrLocationId = null;

        try {
            const qrData = JSON.parse(decodedText);
            if (qrData.token) {
                qrToken = qrData.token;
                qrLocationId = qrData.location_id;
            }
        } catch (e) {
            // Use decodedText as token directly
        }

        const locationSelect = document.getElementById('location-select');
        const locationId = qrLocationId || (locationSelect ? locationSelect.value : null);

        if (!locationId) {
            statusDiv.className = 'status-card warning';
            statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Please select a location first.';
            return;
        }

        if (html5QrCode) {
            await html5QrCode.pause();
        }

        statusDiv.className = 'status-card info';
        statusDiv.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing scan...';

        try {
            const response = await fetch('{{ route('qr.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    token: qrToken,
                    student_id: '{{ auth()->user()->student->id }}',
                    location_id: locationId
                })
            });

            const data = await response.json();

            if (data.success) {
                statusDiv.className = 'status-card success';
                statusDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>Success!</strong> ' + data.message;

                setTimeout(() => {
                    window.location.href = '{{ route('student.dashboard') }}';
                }, 2000);
            } else {
                statusDiv.className = 'status-card danger';
                statusDiv.innerHTML = '<i class="bi bi-x-circle me-2"></i>' + (data.error || 'Scan failed. Please try again.');

                if (html5QrCode) {
                    await html5QrCode.resume();
                }
            }
        } catch (error) {
            console.error('Error:', error);
            statusDiv.className = 'status-card danger';
            statusDiv.innerHTML = '<i class="bi bi-wifi-off me-2"></i>Network error. Please try again.';

            if (html5QrCode) {
                await html5QrCode.resume();
            }
        }
    }

    function onScanFailure(error) {
        // Ignore scan failures
    }

    // Manual QR Code Entry
    document.getElementById('submit-manual-qr').addEventListener('click', async function() {
        const manualInput = document.getElementById('manual-qr-code');
        const qrCode = manualInput.value.trim();

        if (!qrCode) {
            statusDiv.className = 'status-card warning';
            statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Please enter a QR code.';
            return;
        }

        await onScanSuccess(qrCode);
        manualInput.value = '';
    });

    document.getElementById('manual-qr-code').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('submit-manual-qr').click();
        }
    });
</script>
@endsection
