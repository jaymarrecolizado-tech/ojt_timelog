<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $student = $user->student;
        
        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student profile not found']);
        }

        $today = now()->toDateString();
        $todayLogs = TimeLog::where('student_id', $student->id)
            ->where('date', $today)
            ->orderBy('timestamp')
            ->get();

        // Calculate hours today
        $hoursToday = $this->calculateHoursForDay($todayLogs);

        // Get current month's accumulated hours
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        
        $monthLogs = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $accumulatedHours = 0;
        $logsByDate = $monthLogs->groupBy('date');
        foreach ($logsByDate as $date => $logs) {
            $accumulatedHours += $this->calculateHoursForDay($logs);
        }

        $remainingHours = max(0, $student->required_hours - $accumulatedHours);
        $completionPercentage = $student->required_hours > 0 
            ? min(100, round(($accumulatedHours / $student->required_hours) * 100, 1)) 
            : 0;

        // Determine current status
        $statusMap = [
            0 => 'Not clocked in',
            1 => 'Clocked In (AM)',
            2 => 'Clocked Out (AM)',
            3 => 'Clocked In (PM)',
            4 => 'Done for the day',
        ];
        $currentStatus = $statusMap[$todayLogs->count()] ?? 'Done for the day';

        return view('student.dashboard', compact(
            'student',
            'todayLogs',
            'hoursToday',
            'accumulatedHours',
            'remainingHours',
            'completionPercentage',
            'currentStatus'
        ));
    }

    public function logs(Request $request)
    {
        $student = Auth::user()->student;
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->endOfMonth()->toDateString());

        $logs = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('date');

        $days = [];
        $current = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);

        while ($current <= $end) {
            $date = $current->toDateString();
            $dayLogs = $logs->get($date, collect());
            
            $hours = $this->calculateHoursForDay($dayLogs);
            
            $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
            $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
            $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
            $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

            $dayData = [
                'date' => $current->format('F d, Y'),
                'day_name' => $current->format('l'),
                'am_in' => $amIn?->timestamp?->format('h:i A'),
                'am_out' => $amOut?->timestamp?->format('h:i A'),
                'pm_in' => $pmIn?->timestamp?->format('h:i A'),
                'pm_out' => $pmOut?->timestamp?->format('h:i A'),
                'hours' => $hours,
                'status' => $this->getDayStatus($current, $dayLogs, $hours),
            ];

            $days[] = $dayData;
            $current->addDay();
        }

        return view('student.logs', compact('days', 'fromDate', 'toDate'));
    }

    public function scan()
    {
        $student = Auth::user()->student;
        $todayLogs = TimeLog::where('student_id', $student->id)
            ->where('date', now()->toDateString())
            ->count();

        if ($todayLogs >= 4) {
            return redirect()->route('student.dashboard')->with('info', 'You have completed all scans for today.');
        }

        // Determine next scan type
        $nextType = $this->getNextScanType($todayLogs);
        
        // Get active locations for scanning
        $locations = \App\Models\Location::where('is_active', true)->get();
        
        return view('student.scan', compact('nextType', 'locations'));
    }

    public function profile()
    {
        $student = Auth::user()->student;
        return view('student.profile', compact('student'));
    }

    private function calculateHoursForDay($logs)
    {
        $hours = 0;
        $amIn = $logs->firstWhere(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
        $amOut = $logs->firstWhere(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
        $pmIn = $logs->firstWhere(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
        $pmOut = $logs->firstWhere(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

        if ($amIn && $amOut) {
            $hours += $amIn->timestamp->diffInHours($amOut->timestamp);
        }

        if ($pmIn && $pmOut) {
            $hours += $pmIn->timestamp->diffInHours($pmOut->timestamp);
        }

        return $hours;
    }

    private function getDayStatus($date, $logs, $hours)
    {
        if ($logs->isEmpty()) {
            if ($date->isWeekend()) {
                return $date->isSaturday() ? 'SATURDAY' : 'SUNDAY';
            }
            return 'ABSENT';
        }

        return $hours >= 7.5 ? 'COMPLETE' : 'INCOMPLETE';
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
