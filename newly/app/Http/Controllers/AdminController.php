<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\TimeLog;
use App\Models\Location;
use App\Models\Holiday;
use App\Models\SystemSetting;
use App\Models\LogOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalStudents = Student::count();
        $today = now()->toDateString();
        
        $presentToday = TimeLog::where('date', $today)
            ->distinct('student_id')
            ->count('student_id');

        $absentToday = max(0, $totalStudents - $presentToday);

        $graceMinutes = (int) SystemSetting::where('setting_key', 'grace_period_minutes')->first()?->setting_value ?? 15;
        $scheduleStart = SystemSetting::where('setting_key', 'schedule_am_start')->first()?->setting_value ?? '08:00';
        
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
            
            $student = Student::find($log->student_id);
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

        return view('admin.dashboard', compact(
            'totalStudents',
            'presentToday',
            'absentToday',
            'lateCount',
            'clockedIn'
        ));
    }

    public function students(Request $request)
    {
        $query = Student::query();

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('student_id_no', 'like', "%$search%");
            });
        }

        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query->paginate(20);

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

    public function studentDetail($id)
    {
        $student = Student::findOrFail($id);
        
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $logs = TimeLog::where('student_id', $student->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->orderBy('date')
            ->orderBy('timestamp')
            ->get()
            ->groupBy('date');

        return view('admin.student_detail', compact('student', 'logs'));
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

    private function generateProgressReport()
    {
        $students = Student::with(['timeLogs' => function($q) {
            $q->whereMonth('date', now()->month);
        }])->get();

        $pdf = PDF::loadView('admin.reports.progress', compact('students'));
        return $pdf->download('OJT_Progress_Report_' . now()->format('Y-m') . '.pdf');
    }

    private function generateLateReport()
    {
        $today = now()->toDateString();
        $graceMinutes = (int) SystemSetting::where('setting_key', 'grace_period_minutes')->first()?->setting_value ?? 15;
        $scheduleStart = SystemSetting::where('setting_key', 'schedule_am_start')->first()?->setting_value ?? '08:00';
        
        $lateLogs = TimeLog::where('date', $today)
            ->where('log_type', 'IN')
            ->where('log_category', 'AM')
            ->get()
            ->filter(function($log) use ($today, $scheduleStart, $graceMinutes) {
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
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            $setting = SystemSetting::where('setting_key', $key)->first();
            if ($setting) {
                $setting->update([
                    'setting_value' => $value,
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        return back()->with('success', 'Settings updated successfully');
    }

    public function locations()
    {
        $locations = Location::all();
        return view('admin.locations', compact('locations'));
    }

    public function createLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meters' => 'nullable|integer',
        ]);

        Location::create([
            'id' => Str::uuid(),
            'secret_key' => Str::random(64),
            'is_active' => true,
            ...$validated,
        ]);

        return back()->with('success', 'Location created successfully');
    }

    public function addManualLog(Request $request, $studentId)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'log_type' => 'required|in:IN,OUT',
            'log_category' => 'required|in:AM,PM',
            'reason' => 'required|string|max:500',
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

        return back()->with('success', 'Manual time log added successfully');
    }
}
