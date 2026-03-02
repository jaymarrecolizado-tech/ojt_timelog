<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OJT Progress Report</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
            size: A4 landscape;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header p {
            color: #666;
            margin: 0;
            font-size: 8px;
        }
        .summary {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 5px 0 8px 0;
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }
        .summary-item {
            text-align: center;
            flex: 1;
            padding: 6px 8px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 3px;
            min-width: 70px;
        }
        .summary-item .number {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
            line-height: 1;
            display: block;
        }
        .summary-item .label {
            font-size: 6px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            display: block;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #2d3748;
            padding: 4px 3px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #edf2f7;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            letter-spacing: 0.5px;
            color: #1a202c;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .col-id { width: 25px; text-align: center; }
        .col-student-id { width: 55px; }
        .col-name { width: 100px; }
        .col-dept { width: 85px; }
        .col-days { width: 30px; text-align: center; }
        .col-hours { width: 35px; text-align: center; }
        .col-required { width: 35px; text-align: center; }
        .col-remaining { width: 40px; text-align: center; }
        .col-progress { width: 80px; }
        .col-est { width: 60px; }
        .col-status { width: 50px; text-align: center; }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .progress-bar-bg {
            background-color: #e2e8f0;
            height: 8px;
            border-radius: 2px;
            flex: 1;
            min-width: 30px;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 2px;
        }
        .progress-fill.low { background-color: #e53e3e; }
        .progress-fill.medium { background-color: #dd6b20; }
        .progress-fill.high { background-color: #38a169; }
        .progress-fill.complete { background-color: #3182ce; }
        .percentage-text {
            font-weight: bold;
            font-size: 7px;
            min-width: 28px;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background-color: #c6f6d5; color: #22543d; }
        .status-completed { background-color: #bee3f8; color: #2a4365; }
        .status-inactive { background-color: #fed7d7; color: #742a2a; }
        .status-pending { background-color: #fefcbf; color: #744210; }
        .footer {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e0;
            text-align: center;
            font-size: 7px;
            color: #718096;
        }
        .legend {
            margin-top: 5px;
            padding: 4px 8px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            font-size: 6px;
        }
        .legend span {
            display: inline-block;
            margin-right: 10px;
        }
        .legend-color {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 2px;
            vertical-align: middle;
            border-radius: 1px;
        }
        .text-completed { color: #38a169; font-weight: bold; font-size: 7px; }
        .text-na { color: #a0aec0; font-size: 7px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>OJT Progress Report</h1>
        <p>As of {{ now()->format('F d, Y') }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    {{-- Summary Section - Compact --}}
    @php
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'active')->count();
        $completedStudents = $students->where('status', 'completed')->count();
        $totalHoursCompleted = $students->sum('total_hours');
        $avgPercentage = $totalStudents > 0 ? $students->sum('percentage') / $totalStudents : 0;
    @endphp

    <div class="summary">
        <div class="summary-item">
            <span class="number">{{ $totalStudents }}</span>
            <span class="label">Total</span>
        </div>
        <div class="summary-item">
            <span class="number">{{ $activeStudents }}</span>
            <span class="label">Active</span>
        </div>
        <div class="summary-item">
            <span class="number">{{ $completedStudents }}</span>
            <span class="label">Done</span>
        </div>
        <div class="summary-item">
            <span class="number">{{ number_format($totalHoursCompleted) }}</span>
            <span class="label">Hours</span>
        </div>
        <div class="summary-item">
            <span class="number">{{ number_format($avgPercentage, 1) }}%</span>
            <span class="label">Progress</span>
        </div>
    </div>

    {{-- Detailed Progress Table --}}
    <table>
        <thead>
            <tr>
                <th class="col-id">#</th>
                <th class="col-student-id">ID No</th>
                <th class="col-name">Student Name</th>
                <th class="col-dept">Department</th>
                <th class="col-days">Days</th>
                <th class="col-hours">Hrs</th>
                <th class="col-required">Req</th>
                <th class="col-remaining">Left</th>
                <th class="col-progress">Progress</th>
                <th class="col-est">Est. Done</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; @endphp
            @forelse($students as $student)
                @php
                    $progressClass = $student->percentage >= 100 ? 'complete' :
                        ($student->percentage < 30 ? 'low' : ($student->percentage < 70 ? 'medium' : 'high'));
                    $statusClass = 'status-' . $student->status;
                @endphp
                <tr>
                    <td class="col-id">{{ $count++ }}</td>
                    <td class="col-student-id">{{ $student->student_id_no }}</td>
                    <td class="col-name">{{ $student->full_name }}</td>
                    <td class="col-dept">{{ $student->department }}</td>
                    <td class="col-days">{{ $student->days_worked }}</td>
                    <td class="col-hours">{{ number_format($student->total_hours, 1) }}</td>
                    <td class="col-required">{{ $student->required_hours }}</td>
                    <td class="col-remaining">{{ number_format($student->remaining_hours, 1) }}</td>
                    <td class="col-progress">
                        <div class="progress-container">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill {{ $progressClass }}" style="width: {{ min(100, $student->percentage) }}%"></div>
                            </div>
                            <span class="percentage-text">{{ number_format($student->percentage, 0) }}%</span>
                        </div>
                    </td>
                    <td class="col-est">
                        @if($student->remaining_hours <= 0)
                            <span class="text-completed">Done</span>
                        @elseif($student->estimated_completion)
                            <span style="font-size: 7px;">{{ $student->estimated_completion->format('M d') }}</span>
                        @else
                            <span class="text-na">-</span>
                        @endif
                    </td>
                    <td class="col-status">
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst(substr($student->status, 0, 4)) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 15px; color: #a0aec0; font-style: italic;">No students found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Legend --}}
    <div class="legend">
        <strong>Progress:</strong>
        <span><span class="legend-color" style="background-color: #e53e3e;"></span>&lt;30%</span>
        <span><span class="legend-color" style="background-color: #dd6b20;"></span>30-70%</span>
        <span><span class="legend-color" style="background-color: #38a169;"></span>&gt;70%</span>
        <span><span class="legend-color" style="background-color: #3182ce;"></span>100%</span>
    </div>

    <div class="footer">
        OJT Time Logging and Monitoring System | Progress Report | {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
