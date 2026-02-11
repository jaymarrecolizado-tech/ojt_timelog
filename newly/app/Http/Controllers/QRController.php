<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Student;
use App\Models\Location;
use App\Models\LogOverride;
use App\Constants\AppConstants;
use App\Services\ScanTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QRController extends Controller
{
    protected ScanTypeService $scanTypeService;

    public function __construct(ScanTypeService $scanTypeService)
    {
        $this->scanTypeService = $scanTypeService;
    }
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'student_id' => 'required|string',
            'location_id' => 'required|string',
        ]);

        $student = Student::find($validated['student_id']);
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $location = Location::find($validated['location_id']);
        if (!$location || !$location->is_active) {
            return response()->json(['error' => 'Invalid location'], 400);
        }

        // Verify HMAC signature
        $parts = explode('.', $validated['token']);
        if (count($parts) !== 3) {
            return response()->json(['error' => 'Invalid QR code format'], 400);
        }

        [$randomPart, $timestamp, $signature] = $parts;
        
        // Recreate expected signature
        $expectedSignature = hash_hmac('sha256', $randomPart . '.' . $timestamp, $location->secret_key);
        
        // Verify signature
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid QR code signature'], 400);
        }

        // Check expiration
        $tokenTime = base64_decode($timestamp);
        if (now()->timestamp - $tokenTime > AppConstants::QR_TOKEN_EXPIRY_SECONDS) {
            return response()->json(['error' => 'QR code has expired'], 400);
        }

        // Validate QR token hash
        $tokenHash = hash('sha256', $validated['token']);
        $recentLog = TimeLog::where('qr_token_hash', $tokenHash)
            ->where('timestamp', '>', now()->subMinutes(AppConstants::QR_TOKEN_REUSE_WINDOW_MINUTES))
            ->first();

        if ($recentLog) {
            return response()->json(['error' => 'QR code already used'], 400);
        }

        $today = now()->toDateString();
        $todayLogs = TimeLog::where('student_id', $student->id)
            ->where('date', $today)
            ->count();

        if ($todayLogs >= AppConstants::MAX_DAILY_SCANS) {
            return response()->json(['error' => 'Maximum scans reached for today'], 400);
        }

        // Determine log type and category
        $nextScan = $this->scanTypeService->getNextScanType($todayLogs);
        if (!$nextScan) {
            return response()->json(['error' => 'No more scans available'], 400);
        }

        // Create time log
        $timeLog = TimeLog::create([
            'id' => Str::uuid(),
            'student_id' => $student->id,
            'log_type' => $nextScan['type'],
            'log_category' => $nextScan['category'],
            'timestamp' => now(),
            'date' => $today,
            'qr_token_hash' => $tokenHash,
            'location_id' => $location->id,
            'ip_address' => $request->ip(),
            'is_manual' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully clocked {$nextScan['label']}",
            'data' => [
                'student_name' => $student->full_name,
                'log_type' => $nextScan['type'],
                'log_category' => $nextScan['category'],
                'timestamp' => $timeLog->timestamp->toIso8601String(),
            ],
        ]);
    }

    public function generate()
    {
        $location = Location::where('is_active', true)->first();

        if (!$location) {
            return response()->json(['error' => 'No active locations found'], 400);
        }

        $randomPart = Str::random(AppConstants::QR_RANDOM_PART_LENGTH);
        $timestamp = base64_encode((string) now()->timestamp);

        $signature = hash_hmac('sha256', $randomPart . '.' . $timestamp, $location->secret_key);

        $token = $randomPart . '.' . $timestamp . '.' . $signature;

        $expiresAt = now()->addSeconds(AppConstants::QR_TOKEN_EXPIRY_SECONDS);

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'location_id' => $location->id,
        ]);
    }

}
