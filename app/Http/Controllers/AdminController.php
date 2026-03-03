<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\TimeLog;
use App\Models\Location;
use App\Models\Holiday;
use App\Models\SystemSetting;
use App\Models\LogOverride;
use App\Constants\AppConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected function getSystemSetting(string $key, $default = null)
    {
        return Cache::remember("setting:{$key}", AppConstants::CACHE_SETTINGS_TTL, function () use ($key, $default) {
            return SystemSetting::where('setting_key', $key)->first()?->setting_value ?? $default;
        });
    }

    protected function getScheduleSettings(): array
    {
        return Cache::remember('schedule_settings', AppConstants::CACHE_SETTINGS_TTL, function () {
            return [
                'grace_minutes' => (int) $this->getSystemSetting('grace_period_minutes', AppConstants::DEFAULT_GRACE_PERIOD_MINUTES),
                'schedule_start' => $this->getSystemSetting('schedule_am_start', AppConstants::DEFAULT_SCHEDULE_START),
            ];
        });
    }
    public function dashboard()
    {
        $totalStudents = Student::count();
        $today = now()->toDateString();

        $presentToday = TimeLog::where('date', $today)
            ->distinct('student_id')
            ->count('student_id');

        $absentToday = max(0, $totalStudents - $presentToday);

        $scheduleSettings = $this->getScheduleSettings();
        $graceMinutes = $scheduleSettings['grace_minutes'];
        $scheduleStart = $scheduleSettings['schedule_start'];

        $amIns = TimeLog::where('date', $today)
            ->where('log_type', 'IN')
            ->where('log_category', 'AM')
            ->get();

        $lateCount = 0;
        foreach ($amIns as $log) {
            $scheduledTime = Carbon::parse("$today $scheduleStart");
            $graceEnd = $scheduledTime->copy()->addMinutes($graceMinutes);
            if ($log->timestamp > $graceEnd) {
                $lateCount++;
            }
        }

        $allIns = TimeLog::where('date', $today)
            ->where('log_type', 'IN')
            ->with('student')
            ->orderBy('timestamp', 'desc')
            ->get();

        $allOuts = TimeLog::where('date', $today)
            ->where('log_type', 'OUT')
            ->get();

        $outStudentIds = $allOuts->pluck('student_id')->toArray();

        $clockedIn = [];
        $seen = [];
        foreach ($allIns as $log) {
            if (in_array($log->student_id, $outStudentIds) || in_array($log->student_id, $seen)) {
                continue;
            }
            $seen[] = $log->student_id;

            $student = $log->student;
            if ($student) {
                $clockedIn[] = [
                    'student_id' => $student->id,
                    'student_id_no' => $student->student_id_no,
                    'name' => $student->full_name,
                    'department' => $student->department,
                    'clocked_in_at' => $log->timestamp->format('h:i A'),
                    'category' => $log->log_category,
                ];
            }
        }

        $activeLocations = Location::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $locationsData = $activeLocations->map(function ($l) {
            return [
                'name' => $l->name,
                'lat' => (float) number_format($l->latitude, 6),
                'lng' => (float) number_format($l->longitude, 6),
                'radius' => (int) $l->radius_meters
            ];
        })->values();

        return view('admin.dashboard', compact(
            'totalStudents',
            'presentToday',
            'absentToday',
            'lateCount',
            'clockedIn',
            'activeLocations',
            'locationsData'
        ));
    }

    public function students(Request $request)
    {
        $query = Student::query();

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('student_id_no', 'like', "%$search%");
            });
        }

        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query->paginate(AppConstants::PAGINATION_STUDENTS);

        return view('admin.students', compact('students'));
    }

    public function createStudent(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'student_id_no' => 'required|unique:students,student_id_no',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'department' => 'required|string|max:150',
            'program' => 'required|string|max:150',
            'password' => 'required|min:8',
        ]);

        $userId = Str::uuid();
        $user = User::create([
            'id' => $userId,
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => 'student',
            'is_active' => true,
            'email_verified' => true,
        ]);

        Student::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'student_id_no' => $validated['student_id_no'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'department' => $validated['department'],
            'program' => $validated['program'],
            'status' => 'active',
        ]);

        return redirect()->route('admin.students')->with('success', 'Student created successfully');
    }

    public function studentDetail($id, Request $request)
    {
        $student = Student::findOrFail($id);

        $month = $request->input('month', now()->format('Y-m'));

        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $logsQuery = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->with(['student', 'location'])
            ->orderBy('date')
            ->orderBy('timestamp');

        $logsPaginated = $logsQuery->paginate(AppConstants::PAGINATION_LOGS_ADMIN);

        $logs = $logsPaginated->getCollection()->groupBy('date');

        return view('admin.student_detail', compact('student', 'logs', 'logsPaginated', 'month'));
    }

    public function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'department' => 'required|string|max:150',
            'program' => 'required|string|max:150',
            'company' => 'nullable|string|max:200',
            'supervisor_name' => 'nullable|string|max:150',
            'ojt_start' => 'nullable|date',
            'ojt_end' => 'nullable|date',
            'required_hours' => 'nullable|numeric',
            'contact_no' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,completed',
        ]);

        $student->update($validated);

        return back()->with('success', 'Student updated successfully');
    }

    public function reports(Request $request)
    {
        if ($request->has('student_id') && $request->has('from_date')) {
            return $this->generateDTR($request);
        }

        if ($request->input('type') === 'progress') {
            return $this->generateProgressReport();
        }

        if ($request->input('type') === 'late') {
            return $this->generateLateReport();
        }

        if ($request->input('type') === 'attendance') {
            return $this->generateAttendanceReport($request);
        }

        return view('admin.reports');
    }

    public function bulkDTRForm()
    {
        $schools = Student::distinct()
            ->whereNotNull('school_university')
            ->where('school_university', '!=', '')
            ->pluck('school_university')
            ->sort()
            ->values();

        $departments = Student::distinct()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department')
            ->sort()
            ->values();

        return view('admin.reports.bulk_dtr', compact('schools', 'departments'));
    }

    /**
     * Generate bulk Daily Time Records for multiple students
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateBulkDTR(Request $request)
    {
        $validated = $request->validate([
            'school_university' => 'nullable|string|max:255|regex:/^[\w\s\-.,\']+$/',
            'department' => 'nullable|string|max:255|regex:/^[\w\s\-.,\']+$/',
            'status' => 'nullable|in:active,completed,inactive,pending',
            'from_date' => 'required|date|before_or_equal:to_date|after:-2 years',
            'to_date' => 'required|date|after_or_equal:from_date|before:+1 year',
            'output_format' => 'required|in:single,zip',
        ], [
            'from_date.after' => 'Date range cannot start more than 2 years ago.',
            'to_date.before' => 'Date range cannot extend more than 1 year into the future.',
        ]);

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = Carbon::parse($validated['to_date']);
        $daysDiff = $fromDate->diffInDays($toDate);

        // Limit single PDF to 90 days max
        if ($daysDiff > 90 && $validated['output_format'] === 'single') {
            return back()->withInput()->with('error', 'Single PDF format is limited to 90 days. Use ZIP format for longer ranges.');
        }

        $query = Student::query();

        if (!empty($validated['school_university'])) {
            $query->where('school_university', $validated['school_university']);
        }

        if (!empty($validated['department'])) {
            $query->where('department', $validated['department']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Limit to 500 students max to prevent memory issues
        $students = $query->orderBy('last_name')->orderBy('first_name')->limit(500)->get();

        if ($students->isEmpty()) {
            return back()->withInput()->with('error', 'No students found matching the selected criteria.');
        }

        if ($students->count() >= 500) {
            return back()->withInput()->with('warning', 'Results limited to 500 students. Please refine your filters for better results.');
        }

        // Fix N+1 query: Load all logs at once instead of per-student queries
        $studentIds = $students->pluck('id');
        $allLogs = TimeLog::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$validated['from_date'], $validated['to_date']])
            ->orderBy('date')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $student->logs = $allLogs->get($student->id, collect())->groupBy('date');
        }

        $fromDateStr = $validated['from_date'];
        $toDateStr = $validated['to_date'];

        if ($validated['output_format'] === 'single') {
            return $this->generateCombinedPDF($students, $fromDateStr, $toDateStr);
        }

        return $this->generateZIPDTR($students, $fromDateStr, $toDateStr);
    }

    /**
     * Generate combined PDF with all students
     *
     * @param \Illuminate\Database\Eloquent\Collection $students
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Http\Response
     */
    private function generateCombinedPDF($students, $fromDate, $toDate)
    {
        $pdf = PDF::loadView('admin.reports.bulk_dtr_pdf', compact('students', 'fromDate', 'toDate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Bulk_DTR_{$fromDate}_to_{$toDate}.pdf");
    }

    /**
     * Generate ZIP file with individual PDFs for each student
     *
     * @param \Illuminate\Database\Eloquent\Collection $students
     * @param string $fromDate
     * @param string $toDate
     * @return \Illuminate\Http\Response
     */
    private function generateZIPDTR($students, $fromDate, $toDate)
    {
        $zip = new \ZipArchive();
        $zipFilename = tempnam(sys_get_temp_dir(), 'bulk_dtr_') . '.zip';

        try {
            if ($zip->open($zipFilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                \Log::error('Failed to create ZIP file for bulk DTR', [
                    'filename' => $zipFilename,
                    'error' => $zip->getStatusString()
                ]);
                throw new \Exception('Failed to create ZIP file. Please try again.');
            }

            foreach ($students as $student) {
                $pdf = PDF::loadView('admin.reports.dtr', [
                    'student' => $student,
                    'logs' => $student->logs,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                ])->setPaper('a4', 'portrait');

                $pdfContent = $pdf->output();

                // Sanitize student ID before using in filename
                $safeStudentId = preg_replace('/[^A-Za-z0-9._-]/', '_', $student->student_id_no);
                $safeStudentId = substr($safeStudentId, 0, 50); // Limit length
                $filename = "DTR_{$safeStudentId}_{$fromDate}_to_{$toDate}.pdf";
                $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

                // Ensure filename doesn't exceed filesystem limits
                if (strlen($safeFilename) > 255) {
                    $safeFilename = substr($safeFilename, 0, 255);
                }

                $zip->addFromString($safeFilename, $pdfContent);
            }

            $zip->close();

            return response()->download($zipFilename, "Bulk_DTR_{$fromDate}_to_{$toDate}.zip")
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Ensure cleanup on error
            if (file_exists($zipFilename)) {
                @unlink($zipFilename);
            }
            \Log::error('Bulk DTR ZIP generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to generate ZIP file: ' . $e->getMessage());
        }
    }

    private function generateDTR(Request $request)
    {
        $student = Student::findOrFail($request->input('student_id'));
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $logs = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('date');

        $pdf = PDF::loadView('admin.reports.dtr', compact('student', 'logs', 'fromDate', 'toDate'));
        return $pdf->download("DTR_{$student->student_id_no}_{$fromDate}_to_{$toDate}.pdf");
    }

    /**
     * Generate OJT progress report showing all student progress
     *
     * @return \Illuminate\Http\Response
     */
    private function generateProgressReport()
    {
        // Load ALL time logs for each student, not just current month
        $students = Student::with([
            'timeLogs' => function ($q) {
                $q->orderBy('date');
            }
        ])->get();

        // Calculate progress metrics for each student
        foreach ($students as $student) {
            $totalHours = 0;
            $daysWorked = 0;
            $logsByDate = $student->timeLogs->groupBy('date');

            foreach ($logsByDate as $date => $dayLogs) {
                $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

                $dayHours = 0;
                if ($amIn && $amOut) {
                    $dayHours += $amIn->timestamp->diffInMinutes($amOut->timestamp) / 60;
                }
                if ($pmIn && $pmOut) {
                    $dayHours += $pmIn->timestamp->diffInMinutes($pmOut->timestamp) / 60;
                }

                // Cap daily hours at 8
                $totalHours += min($dayHours, 8);
                if ($dayHours > 0) {
                    $daysWorked++;
                }
            }

            $requiredHours = $student->required_hours ?? 500;
            $remainingHours = max(0, $requiredHours - $totalHours);
            $percentage = $requiredHours > 0 ? min(100, ($totalHours / $requiredHours) * 100) : 0;

            // Calculate estimated completion date (assuming 8 hours/day, 5 days/week)
            if ($totalHours > 0 && $remainingHours > 0) {
                $avgHoursPerDay = $totalHours / max(1, $daysWorked);
                $estimatedDaysLeft = ceil($remainingHours / min(8, $avgHoursPerDay));
                $estimatedDate = now()->addWeekdays($estimatedDaysLeft);
            } else {
                $estimatedDate = null;
            }

            // Attach calculated metrics to student
            $student->total_hours = $totalHours;
            $student->required_hours = $requiredHours;
            $student->remaining_hours = $remainingHours;
            $student->percentage = $percentage;
            $student->days_worked = $daysWorked;
            $student->estimated_completion = $estimatedDate;
        }

        $pdf = PDF::loadView('admin.reports.progress', compact('students'));
        return $pdf->download('OJT_Progress_Report_' . now()->format('Y-m-d') . '.pdf');
    }

    private function generateLateReport()
    {
        $today = now()->toDateString();
        $scheduleSettings = $this->getScheduleSettings();
        $graceMinutes = $scheduleSettings['grace_minutes'];
        $scheduleStart = $scheduleSettings['schedule_start'];

        $lateLogs = TimeLog::where('date', $today)
            ->where('log_type', 'IN')
            ->where('log_category', 'AM')
            ->get()
            ->filter(function ($log) use ($today, $scheduleStart, $graceMinutes) {
                $scheduledTime = Carbon::parse("$today $scheduleStart");
                $graceEnd = $scheduledTime->copy()->addMinutes($graceMinutes);
                return $log->timestamp > $graceEnd;
            });

        $pdf = PDF::loadView('admin.reports.late', compact('lateLogs', 'today'));
        return $pdf->download('Late_Report_' . $today . '.pdf');
    }

    private function generateAttendanceReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $department = $request->input('department');

        $query = Student::query();
        if ($department) {
            $query->where('department', $department);
        }
        $students = $query->get();

        $pdf = PDF::loadView('admin.reports.attendance', compact('students', 'fromDate', 'toDate', 'department'));
        return $pdf->download('Attendance_Report_' . $fromDate . '_to_' . $toDate . '.pdf');
    }

    public function settings()
    {
        $settings = SystemSetting::all();
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'qr_rotation_seconds' => 'nullable|integer|min:10|max:300',
            'max_scans_per_day' => 'nullable|integer|min:1|max:10',
            'grace_period_minutes' => 'nullable|integer|min:0|max:60',
            'schedule_am_start' => 'nullable|date_format:H:i',
            'schedule_am_end' => 'nullable|date_format:H:i|after:schedule_am_start',
            'schedule_pm_start' => 'nullable|date_format:H:i',
            'schedule_pm_end' => 'nullable|date_format:H:i|after:schedule_pm_start',
            'geolocation_required' => 'nullable|in:true,false,1,0',
            'geolocation_max_distance' => 'nullable|integer|min:10|max:1000',
            'scan_debounce_seconds' => 'nullable|integer|min:5|max:300',
        ], [
            'schedule_am_end.after' => 'AM end time must be after AM start time',
            'schedule_pm_end.after' => 'PM end time must be after PM start time',
            'geolocation_required.in' => 'Invalid value for geolocation required setting',
            'qr_rotation_seconds.integer' => 'QR rotation must be a number',
            'qr_rotation_seconds.min' => 'QR rotation must be at least 10 seconds',
            'qr_rotation_seconds.max' => 'QR rotation cannot exceed 300 seconds',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $setting = SystemSetting::where('setting_key', $key)->first();
                if ($setting) {
                    $setting->update([
                        'setting_value' => $value,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Settings updated successfully');
    }

    public function locations()
    {
        $locations = Location::paginate(AppConstants::PAGINATION_LOCATIONS);

        $activeLocations = $locations->filter(function ($l) {
            return $l->latitude && $l->longitude && $l->is_active;
        })->map(function ($l) {
            return [
                'name' => $l->name,
                'lat' => (float) number_format($l->latitude, 6),
                'lng' => (float) number_format($l->longitude, 6),
                'radius' => (int) $l->radius_meters
            ];
        })->values();

        return view('admin.locations', compact('locations', 'activeLocations'));
    }

    public function createLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:10|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        Location::create([
            'id' => Str::uuid(),
            'secret_key' => Str::random(64),
            'is_active' => $request->boolean('is_active', true),
            ...$validated,
        ]);

        return back()->with('success', 'Location created successfully');
    }

    public function updateLocation(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $location->update($validated);

        return back()->with('success', 'Location updated successfully');
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return back()->with('success', 'Location deleted successfully');
    }

    public function regenerateLocationKey($id)
    {
        $location = Location::findOrFail($id);
        $location->update([
            'secret_key' => Str::random(64)
        ]);

        return back()->with('success', 'Secret key regenerated successfully');
    }

    public function addManualLog(Request $request, $studentId)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'time' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'log_type' => 'required|in:IN,OUT',
            'log_category' => 'required|in:AM,PM',
            'location_id' => 'nullable|exists:locations,id',
            'reason' => 'required|string|max:500|not_regex:/[<>]/',
        ], [
            'time.regex' => 'Time must be in HH:MM format (24-hour format).',
            'reason.not_regex' => 'Reason cannot contain HTML tags for security reasons.',
            'location_id.exists' => 'Selected location does not exist.',
        ]);

        $student = Student::findOrFail($studentId);
        $timestamp = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        $timeLog = TimeLog::create([
            'id' => Str::uuid(),
            'student_id' => $student->id,
            'log_type' => $validated['log_type'],
            'log_category' => $validated['log_category'],
            'timestamp' => $timestamp,
            'date' => $validated['date'],
            'location_id' => $validated['location_id'] ?? null,
            'is_manual' => true,
            'is_flagged' => false,
        ]);

        LogOverride::create([
            'id' => Str::uuid(),
            'time_log_id' => $timeLog->id,
            'student_id' => $student->id,
            'admin_id' => Auth::id(),
            'action' => 'CREATE',
            'new_values' => $timeLog->toArray(),
            'reason' => $validated['reason'],
        ]);

        // Preserve the month filter the admin had open
        $month = $request->input('month', now()->format('Y-m'));
        return redirect()
            ->route('admin.students.detail', [$student->id, 'month' => $month])
            ->with('success', 'Manual time log added for ' . Carbon::parse($validated['date'])->format('M d, Y'));
    }

    public function deleteLog(Request $request, $studentId, $logId)
    {
        $student = Student::findOrFail($studentId);
        $log = TimeLog::where('id', $logId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Record audit trail before deletion
        LogOverride::create([
            'id' => Str::uuid(),
            'time_log_id' => $log->id,
            'student_id' => $student->id,
            'admin_id' => Auth::id(),
            'action' => 'DELETE',
            'old_values' => $log->toArray(),
            'reason' => $request->input('reason', 'Deleted by admin'),
        ]);

        $log->delete();

        $month = $request->input('month', now()->format('Y-m'));
        return redirect()
            ->route('admin.students.detail', [$student->id, 'month' => $month])
            ->with('success', 'Log entry deleted successfully.');
    }
}
