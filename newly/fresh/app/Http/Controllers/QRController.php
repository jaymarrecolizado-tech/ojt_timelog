<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Student;
use App\Models\Location;
use App\Models\LogOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QRController extends Controller
{
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

        // Validate QR token hash
        $tokenHash = hash('sha256', $validated['token']);
        $recentLog = TimeLog::where('qr_token_hash', $tokenHash)
            ->where('timestamp', '>', now()->subMinutes(5))
            ->first();

        if ($recentLog) {
            return response()->json(['error' => 'QR code already used'], 400);
        }

        $today = now()->toDateString();
        $todayLogs = TimeLog::where('student_id', $student->id)
            ->where('date', $today)
            ->count();

        if ($todayLogs >= 4) {
            return response()->json(['error' => 'Maximum scans reached for today'], 400);
        }

        // Determine log type and category
        $nextScan = $this->getNextScanType($todayLogs);
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
        $token = Str::random(32);
        $expiresAt = now()->addSeconds(30);

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    private function getNextScanType($logCount)
    {
        $types = [
            0 => ['type' => 'IN', 'category' => 'AM', 'label' => 'AM IN'],
            1 => ['type' => 'OUT', 'category' => 'AM', 'label' => 'AM OUT'],
            2 => ['type' => 'IN', 'category' => 'PM', 'label' => 'PM IN'],
            3 => ['type' => 'OUT', 'category' => 'PM', 'label' => 'PM OUT'],
        ];

        return $types[$logCount] ?? null;
    }
}
