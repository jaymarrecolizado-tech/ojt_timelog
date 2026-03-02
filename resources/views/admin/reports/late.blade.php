<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Late Comers Report</title>
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
        .late { color: #dc3545; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LATE COMERS REPORT</h1>
        <p>Date: {{ \Carbon\Carbon::parse($today)->format('F d, Y') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Report Generated:</td>
                <td>{{ now()->format('F d, Y h:i A') }}</td>
                <td class="label">Total Late:</td>
                <td>{{ $lateLogs->count() }}</td>
            </tr>
        </table>
    </div>

    @if($lateLogs->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>School/University</th>
                <th>Time In</th>
                <th>Scheduled Time</th>
                <th>Minutes Late</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lateLogs as $log)
                @php
                    $student = $log->student;
                    $scheduleStart = \App\Constants\AppConstants::DEFAULT_SCHEDULE_START;
                    $minutesLate = $log->timestamp->format('H:i') > $scheduleStart 
                        ? $log->timestamp->diffInMinutes(\Carbon\Carbon::parse($today . ' ' . $scheduleStart)) 
                        : 0;
                @endphp
                @if($student)
                <tr>
                    <td>{{ $student->student_id_no }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->department }}</td>
                    <td>{{ $student->school_university ?? 'N/A' }}</td>
                    <td class="late">{{ $log->timestamp->format('h:i A') }}</td>
                    <td>{{ $scheduleStart }}</td>
                    <td class="late">{{ $minutesLate }} min</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; padding: 20px; color: #28a745;">
        <strong>No late arrivals today!</strong>
    </p>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }} | OJT TLMS System</p>
    </div>
</body>
</html>
