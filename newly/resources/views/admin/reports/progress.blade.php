<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OJT Progress Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .progress-bar { background-color: #e0e0e0; height: 15px; border-radius: 3px; }
        .progress-fill { background-color: #4CAF50; height: 100%; border-radius: 3px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>OJT PROGRESS REPORT</h1>
        <p>As of {{ now()->format('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Hours Completed</th>
                <th>Required</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                @php
                    $logs = $student->timeLogs;
                    $hours = 0;
                    $logsByDate = $logs->groupBy('date');
                    foreach ($logsByDate as $date => $dayLogs) {
                        $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                        $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                        $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                        $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');
                        if ($amIn && $amOut) $hours += $amIn->timestamp->diffInHours($amOut->timestamp);
                        if ($pmIn && $pmOut) $hours += $pmIn->timestamp->diffInHours($pmOut->timestamp);
                    }
                    $percentage = $student->required_hours > 0 ? min(100, ($hours / $student->required_hours) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $student->student_id_no }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->department }}</td>
                    <td>{{ number_format($hours, 1) }}</td>
                    <td>{{ $student->required_hours }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                        {{ number_format($percentage, 1) }}%
                    </td>
                    <td>{{ ucfirst($student->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }} | OJT TLMS System</p>
    </div>
</body>
</html>
