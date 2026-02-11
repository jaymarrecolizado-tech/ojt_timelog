<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Constants\AppConstants;
use App\Services\ScanTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends Controller
{
    protected ScanTypeService $scanTypeService;

    public function __construct(ScanTypeService $scanTypeService)
    {
        $this->scanTypeService = $scanTypeService;
    }
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
            ? min(AppConstants::MAX_COMPLETION_PERCENT, round(($accumulatedHours / $student->required_hours) * 100, 1))
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
        
        // Validate date range is not too large
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);
        if ($start->diffInDays($end) > AppConstants::MAX_DATE_RANGE_DAYS) {
            return back()->withErrors(['error' => 'Date range cannot exceed ' . AppConstants::MAX_DATE_RANGE_DAYS . ' days']);
        }

        // Paginate the results
        $days = collect();
        $current = $start->copy();

        while ($current <= $end) {
            $days->push($current->copy());
            $current->addDay();
        }

        $perPage = AppConstants::PAGINATION_LOGS_STUDENT;
        $currentPage = $request->input('page', 1);
        $pagedDays = $days->forPage($currentPage, $perPage);
        
        // Get all logs for the date range
        $logs = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('date');
        
        // Build day data for paginated days
        $dayData = [];
        foreach ($pagedDays as $day) {
            $date = $day->toDateString();
            $dayLogs = $logs->get($date, collect());
            
            $hours = $this->calculateHoursForDay($dayLogs);
            
            $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
            $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
            $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
            $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

            $dayData[] = [
                'date' => $day->format('F d, Y'),
                'day_name' => $day->format('l'),
                'am_in' => $amIn?->timestamp?->format('h:i A'),
                'am_out' => $amOut?->timestamp?->format('h:i A'),
                'pm_in' => $pmIn?->timestamp?->format('h:i A'),
                'pm_out' => $pmOut?->timestamp?->format('h:i A'),
                'hours' => $hours,
                'status' => $this->getDayStatus($day, $dayLogs, $hours),
            ];
        }
        
        // Create paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $dayData,
            $days->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('student.logs', compact('dayData', 'fromDate', 'toDate', 'paginator'));
    }

    public function scan()
    {
        $student = Auth::user()->student;
        $todayLogs = TimeLog::where('student_id', $student->id)
            ->where('date', now()->toDateString())
            ->count();

        if ($todayLogs >= AppConstants::MAX_DAILY_SCANS) {
            return redirect()->route('student.dashboard')->with('info', 'You have completed all scans for today.');
        }

        $nextType = $this->scanTypeService->getNextScanType($todayLogs);

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

        return $hours >= AppConstants::REQUIRED_DAILY_HOURS ? 'COMPLETE' : 'INCOMPLETE';
    }

}
