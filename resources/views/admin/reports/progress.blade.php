<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OJT Progress Report</title>
    <style>
        @page {
            margin: 12mm 12mm 12mm 12mm;
            size: A4 landscape;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 3px solid #1a202c;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #1a202c;
            letter-spacing: 3px;
        }
        .header p {
            color: #718096;
            margin: 0;
            font-size: 9px;
        }
        .summary {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 8px 0 10px 0;
            padding: 8px 15px;
            background-color: #f7fafc;
            border-radius: 4px;
            border-left: 4px solid #4299e1;
            border-right: 4px solid #4299e1;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #718096;
            font-weight: 600;
        }
        .summary-item .number {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #cbd5e0;
            padding: 8px 6px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #2d3748;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 1px;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }
        tbody tr:hover {
            background-color: #edf2f7;
        }
        .col-id { width: 35px; text-align: center; font-weight: bold; color: #718096; }
        .col-student-id { width: 70px; font-family: monospace; }
        .col-name { width: 120px; font-weight: 600; }
        .col-dept { width: 100px; }
        .col-days { width: 40px; text-align: center; }
        .col-hours { width: 50px; text-align: center; font-weight: 600; }
        .col-required { width: 45px; text-align: center; color: #718096; }
        .col-remaining { width: 50px; text-align: center; color: #e53e3e; font-weight: 600; }
        .col-progress { width: 110px; }
        .col-est { width: 70px; }
        .col-status { width: 65px; text-align: center; }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .progress-bar-bg {
            background-color: #e2e8f0;
            height: 16px;
            border-radius: 8px;
            flex: 1;
            min-width: 50px;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 8px;
            position: relative;
            transition: width 0.3s ease;
        }
        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
        }
        .progress-fill.low {
            background-color: #e53e3e;
            background-image: repeating-linear-gradient(45deg, #c53030 0px, #c53030 10px, #e53e3e 10px, #e53e3e 20px);
        }
        .progress-fill.medium {
            background-color: #dd6b20;
            background-image: repeating-linear-gradient(45deg, #c05621 0px, #c05621 10px, #dd6b20 10px, #dd6b20 20px);
        }
        .progress-fill.high {
            background-color: #38a169;
            background-image: repeating-linear-gradient(45deg, #276749 0px, #276749 10px, #38a169 10px, #38a169 20px);
        }
        .progress-fill.complete {
            background-color: #3182ce;
            background-image: repeating-linear-gradient(45deg, #2c5282 0px, #2c5282 10px, #3182ce 10px, #3182ce 20px);
        }
        .percentage-text {
            font-weight: bold;
            font-size: 9px;
            min-width: 35px;
            text-align: right;
        }
        .percentage-low { color: #c53030; }
        .percentage-medium { color: #c05621; }
        .percentage-high { color: #276749; }
        .percentage-complete { color: #2c5282; }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background-color: #48bb78; color: white; }
        .status-completed { background-color: #4299e1; color: white; }
        .status-inactive { background-color: #a0aec0; color: white; }
        .status-pending { background-color: #ed8936; color: white; }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #a0aec0;
        }
        .legend {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #2d3748;
            color: white;
            border-radius: 4px;
            font-size: 8px;
        }
        .legend span {
            display: inline-block;
            margin-right: 20px;
        }
        .legend-color {
            display: inline-block;
            width: 16px;
            height: 12px;
            margin-right: 5px;
            vertical-align: middle;
            border-radius: 3px;
        }
        .text-completed { color: #38a169; font-weight: bold; }
        .text-na { color: #a0aec0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>OJT Progress Report</h1>
        <p>As of {{ now()->format('F d, Y') }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    {{-- Simple Summary --}}
    @php
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'active')->count();
        $completedStudents = $students->where('status', 'completed')->count();
        $totalHoursCompleted = $students->sum('total_hours');
        $avgPercentage = $totalStudents > 0 ? $students->sum('percentage') / $totalStudents : 0;
    @endphp

    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Students</div>
            <div class="number">{{ $totalStudents }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Active</div>
            <div class="number">{{ $activeStudents }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Completed</div>
            <div class="number">{{ $completedStudents }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Hours</div>
            <div class="number">{{ number_format($totalHoursCompleted) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Avg Progress</div>
            <div class="number">{{ number_format($avgPercentage, 1) }}%</div>
        </div>
    </div>

    {{-- Enhanced Progress Table --}}
    <table>
        <thead>
            <tr>
                <th class="col-id">#</th>
                <th class="col-student-id">ID No</th>
                <th class="col-name">Student Name</th>
                <th class="col-dept">Department</th>
                <th class="col-days">Days</th>
                <th class="col-hours">Hours</th>
                <th class="col-required">Req</th>
                <th class="col-remaining">Remaining</th>
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
                    $percentClass = $student->percentage >= 100 ? 'percentage-complete' :
                        ($student->percentage < 30 ? 'percentage-low' : ($student->percentage < 70 ? 'percentage-medium' : 'percentage-high'));
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
                            <span class="percentage-text {{ $percentClass }}">{{ number_format($student->percentage, 1) }}%</span>
                        </div>
                    </td>
                    <td class="col-est">
                        @if($student->remaining_hours <= 0)
                            <span class="text-completed">Done</span>
                        @elseif($student->estimated_completion)
                            <span style="font-size: 9px;">{{ $student->estimated_completion->format('M d, Y') }}</span>
                        @else
                            <span class="text-na">-</span>
                        @endif
                    </td>
                    <td class="col-status">
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($student->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px; color: #a0aec0; font-style: italic;">No students found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Dark Legend --}}
    <div class="legend">
        <strong>Progress:</strong>
        <span><span class="legend-color" style="background-color: #fc8181;"></span>&lt; 30% (Behind)</span>
        <span><span class="legend-color" style="background-color: #f6ad55;"></span>30-70% (On Track)</span>
        <span><span class="legend-color" style="background-color: #68d391;"></span>&gt; 70% (Ahead)</span>
        <span><span class="legend-color" style="background-color: #4299e1;"></span>100% (Complete)</span>
    </div>

    <div class="footer">
        OJT Time Logging and Monitoring System | Progress Report | {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
