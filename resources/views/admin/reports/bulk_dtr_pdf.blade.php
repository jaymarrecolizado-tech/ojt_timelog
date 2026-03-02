<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk DTR - {{ $fromDate }} to {{ $toDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .student-page {
            page-break-after: always;
            padding: 20px;
            min-height: 100vh;
        }
        .student-page:last-child {
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            color: #333;
        }
        .header p {
            font-size: 11px;
            margin: 5px 0;
            color: #666;
        }
        .info {
            margin-bottom: 15px;
        }
        .info table {
            width: 100%;
            border-collapse: collapse;
        }
        .info td {
            padding: 5px 8px;
            border: 1px solid #ddd;
        }
        .info .label {
            font-weight: bold;
            width: 140px;
            background-color: #f5f5f5;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th, table.data td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
        }
        table.data th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.data td {
            font-size: 11px;
        }
        table.data tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total {
            margin-top: 15px;
            padding: 8px;
            background-color: #f0f0f0;
            border: 1px solid #333;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
        .summary-page {
            page-break-after: always;
            padding: 30px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <!-- Summary Page -->
    <div class="summary-page">
        <div class="header">
            <h1>Bulk Daily Time Record Summary</h1>
            <p>Period: {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</p>
            <p>Generated: {{ now()->format('F d, Y h:i A') }}</p>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1; @endphp
                @foreach($students as $student)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ $student->student_id_no }}</td>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->department }}</td>
                        <td>
                            <span style="padding: 2px 8px; border-radius: 3px;
                                @if($student->status === 'active') background-color: #d4edda; color: #155724;
                                @elseif($student->status === 'completed') background-color: #cce5ff; color: #004085;
                                @elseif($student->status === 'inactive') background-color: #f8d7da; color: #721c24;
                                @else background-color: #fff3cd; color: #856404;
                                @endif">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 30px; text-align: center;">
            <p><strong>Total Students:</strong> {{ $students->count() }}</p>
        </div>
    </div>

    <!-- Individual Student DTR Pages -->
    @foreach($students as $student)
        <div class="student-page">
            <div class="header">
                <h1>DAILY TIME RECORD</h1>
                <h2>{{ $student->full_name }}</h2>
                <p>Period: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</p>
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
                    <tr>
                        <td class="label">Status:</td>
                        <td>{{ ucfirst($student->status) }}</td>
                        <td class="label">Required Hours:</td>
                        <td>{{ $student->required_hours ?? 500 }} hours</td>
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
                    @forelse($student->logs as $date => $dayLogs)
                        @php
                            $amIn = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'IN');
                            $amOut = $dayLogs->first(fn($log) => $log->log_category === 'AM' && $log->log_type === 'OUT');
                            $pmIn = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'IN');
                            $pmOut = $dayLogs->first(fn($log) => $log->log_category === 'PM' && $log->log_type === 'OUT');

                            $hours = 0;
                            if ($amIn && $amOut) {
                                $hours += $amIn->timestamp->diffInMinutes($amOut->timestamp) / 60;
                            }
                            if ($pmIn && $pmOut) {
                                $hours += $pmIn->timestamp->diffInMinutes($pmOut->timestamp) / 60;
                            }
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
                            <td>{{ number_format($hours, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-records">No time records found for this period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total">
                Total Hours: {{ number_format($totalHours, 2) }} / {{ $student->required_hours ?? 500 }} required
            </div>

            <div class="footer">
                <p>Generated on {{ now()->format('F d, Y h:i A') }} | OJT TLMS System</p>
            </div>
        </div>
    @endforeach
</body>
</html>
