<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DTR - {{ $student->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #666; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 5px; }
        .info .label { font-weight: bold; width: 150px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 8px; text-align: center; }
        table.data th { background-color: #f0f0f0; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .total { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAILY TIME RECORD</h1>
        <h2>{{ $student->full_name }}</h2>
        <p>Period: {{ $fromDate }} to {{ $toDate }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td class="label">Student ID:</td>
                <td>{{ $student->student_id_no }}</td>
                <td class="label">Department:</td>
                <td>{{ $student->department }}</td>
            </tr>
            <tr>
                <td class="label">Program:</td>
                <td>{{ $student->program }}</td>
                <td class="label">School/University:</td>
                <td>{{ $student->school_university ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>AM IN</th>
                <th>AM OUT</th>
                <th>PM IN</th>
                <th>PM OUT</th>
                <th>Hours</th>
            </tr>
        </thead>
        <tbody>
            @php $totalHours = 0; @endphp
            @forelse($logs as $date => $dayLogs)
                @php
                    $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                    $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                    $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                    $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

                    $hours = 0;
                    if ($amIn && $amOut) $hours += $amIn->timestamp->diffInHours($amOut->timestamp);
                    if ($pmIn && $pmOut) $hours += $pmIn->timestamp->diffInHours($pmOut->timestamp);
                    // Cap daily hours at 8
                    $hours = min($hours, 8);
                    $totalHours += $hours;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                    <td>{{ $amIn ? $amIn->timestamp->format('h:i A') : '--' }}</td>
                    <td>{{ $amOut ? $amOut->timestamp->format('h:i A') : '--' }}</td>
                    <td>{{ $pmIn ? $pmIn->timestamp->format('h:i A') : '--' }}</td>
                    <td>{{ $pmOut ? $pmOut->timestamp->format('h:i A') : '--' }}</td>
                    <td>{{ $hours }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Total Hours: {{ $totalHours }} / {{ $student->required_hours }} required
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }} | OJT TLMS System</p>
    </div>
</body>
</html>
