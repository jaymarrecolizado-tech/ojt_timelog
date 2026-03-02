<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { color: #666; }
        .summary { margin-bottom: 20px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 5px; }
        .summary .label { font-weight: bold; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .present { color: #28a745; }
        .absent { color: #dc3545; }
        .late { color: #ffc107; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ATTENDANCE SUMMARY REPORT</h1>
        <p>Period: {{ $fromDate }} to {{ $toDate }}</p>
        @if($department)
        <p>Department: {{ $department }}</p>
        @else
        <p>Department: All Departments</p>
        @endif
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Report Generated:</td>
                <td>{{ now()->format('F d, Y h:i A') }}</td>
                <td class="label">Total Students:</td>
                <td>{{ $students->count() }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>School/University</th>
                <th>Days Present</th>
                <th>Days Absent</th>
                <th>Total Hours</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                @php
                    $logs = \App\Models\TimeLog::where('student_id', $student->id)
                        ->whereBetween('date', [$fromDate, $toDate])
                        ->get();
                    
                    $daysPresent = $logs->groupBy('date')->count();
                    
                    $totalDays = \Carbon\Carbon::parse($fromDate)->diffInDays(\Carbon\Carbon::parse($toDate)) + 1;
                    $weekdays = 0;
                    $current = \Carbon\Carbon::parse($fromDate);
                    while ($current->lte(\Carbon\Carbon::parse($toDate))) {
                        if ($current->isWeekday()) $weekdays++;
                        $current->addDay();
                    }
                    $daysAbsent = max(0, $weekdays - $daysPresent);
                    
                    $hours = 0;
                    $logsByDate = $logs->groupBy('date');
                    foreach ($logsByDate as $date => $dayLogs) {
                        $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                        $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                        $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                        $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');
                        $dayHours = 0;
                        if ($amIn && $amOut) $dayHours += $amIn->timestamp->diffInHours($amOut->timestamp);
                        if ($pmIn && $pmOut) $dayHours += $pmIn->timestamp->diffInHours($pmOut->timestamp);
                        // Cap daily hours at 8
                        $hours += min($dayHours, 8);
                    }
                    
                    $percentage = $student->required_hours > 0 ? min(100, ($hours / $student->required_hours) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $student->student_id_no }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->department }}</td>
                    <td>{{ $student->school_university ?? 'N/A' }}</td>
                    <td class="present">{{ $daysPresent }}</td>
                    <td class="absent">{{ $daysAbsent }}</td>
                    <td>{{ $hours }}</td>
                    <td>{{ number_format($percentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }} | OJT TLMS System</p>
    </div>
</body>
</html>
