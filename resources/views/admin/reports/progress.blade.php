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
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 3px solid #2d3748;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #1a202c;
            letter-spacing: 2px;
        }
        .header p {
            color: #718096;
            margin: 0;
            font-size: 8px;
        }
        .summary {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 10px 0 12px 0;
        }
        .summary-card {
            flex: 1;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 10px;
            background-color: #f7fafc;
            min-width: 90px;
            position: relative;
        }
        .summary-card.blue {
            background-color: #ebf8ff;
            border-color: #4299e1;
        }
        .summary-card.green {
            background-color: #f0fff4;
            border-color: #48bb78;
        }
        .summary-card.purple {
            background-color: #faf5ff;
            border-color: #9f7aea;
        }
        .summary-card.orange {
            background-color: #fffaf0;
            border-color: #ed8936;
        }
        .summary-card.red {
            background-color: #fff5f5;
            border-color: #f56565;
        }
        .summary-card .card-header {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 5px;
        }
        .summary-card.blue .card-header { color: #2b6cb0; }
        .summary-card.green .card-header { color: #276749; }
        .summary-card.purple .card-header { color: #6b46c1; }
        .summary-card.orange .card-header { color: #c05621; }
        .summary-card.red .card-header { color: #c53030; }
        .summary-card .number {
            font-size: 24px;
            font-weight: bold;
            line-height: 1;
            display: block;
        }
        .summary-card.blue .number { color: #2c5282; }
        .summary-card.green .number { color: #22543d; }
        .summary-card.purple .number { color: #553c9a; }
        .summary-card.orange .number { color: #9c4221; }
        .summary-card.red .number { color: #9b2c2c; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #2d3748;
            padding: 6px 5px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #2d3748;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .col-id { width: 30px; text-align: center; }
        .col-student-id { width: 65px; }
        .col-name { width: 115px; }
        .col-dept { width: 95px; }
        .col-days { width: 35px; text-align: center; }
        .col-hours { width: 45px; text-align: center; }
        .col-required { width: 40px; text-align: center; }
        .col-remaining { width: 45px; text-align: center; }
        .col-progress { width: 100px; }
        .col-est { width: 70px; }
        .col-status { width: 60px; text-align: center; }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .progress-bar-bg {
            background-color: #e2e8f0;
            height: 12px;
            border-radius: 6px;
            flex: 1;
            min-width: 40px;
            border: 1px solid #cbd5e0;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
        }
        .progress-fill.low { background-color: #fc8181; }
        .progress-fill.medium { background-color: #f6ad55; }
        .progress-fill.high { background-color: #68d391; }
        .progress-fill.complete { background-color: #4299e1; }
        .percentage-text {
            font-weight: bold;
            font-size: 8px;
            min-width: 32px;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .status-completed {
            background-color: #bee3f8;
            color: #2a4365;
            border: 1px solid #90cdf4;
        }
        .status-inactive {
            background-color: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        .status-pending {
            background-color: #fefcbf;
            color: #744210;
            border: 1px solid #fbd38d;
        }
        .footer {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #718096;
        }
        .legend {
            margin-top: 8px;
            padding: 8px 12px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 7px;
        }
        .legend span {
            display: inline-block;
            margin-right: 15px;
        }
        .legend-color {
            display: inline-block;
            width: 14px;
            height: 12px;
            margin-right: 4px;
            vertical-align: middle;
            border-radius: 3px;
            border: 1px solid #cbd5e0;
        }
        .text-completed { color: #38a169; font-weight: bold; font-size: 8px; }
        .text-na { color: #a0aec0; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>OJT Progress Report</h1>
        <p>As of {{ now()->format('F d, Y') }} | Generated: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    {{-- Summary Section - Card Style with Solid Colors --}}
    @php
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'active')->count();
        $completedStudents = $students->where('status', 'completed')->count();
        $totalHoursCompleted = $students->sum('total_hours');
        $avgPercentage = $totalStudents > 0 ? $students->sum('percentage') / $totalStudents : 0;
    @endphp

    <div class="summary">
        <div class="summary-card blue">
            <div class="card-header">Total Students</div>
            <span class="number">{{ $totalStudents }}</span>
        </div>
        <div class="summary-card green">
            <div class="card-header">Active</div>
            <span class="number">{{ $activeStudents }}</span>
        </div>
        <div class="summary-card purple">
            <div class="card-header">Completed</div>
            <span class="number">{{ $completedStudents }}</span>
        </div>
        <div class="summary-card orange">
            <div class="card-header">Total Hours</div>
            <span class="number">{{ number_format($totalHoursCompleted) }}</span>
        </div>
        <div class="summary-card red">
            <div class="card-header">Avg Progress</div>
            <span class="number">{{ number_format($avgPercentage, 1) }}%</span>
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
                            <span style="font-size: 8px;">{{ $student->estimated_completion->format('M d') }}</span>
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
                    <td colspan="11" style="text-align: center; padding: 15px; color: #a0aec0; font-style: italic;">No students found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Legend --}}
    <div class="legend">
        <strong>Progress:</strong>
        <span><span class="legend-color" style="background-color: #fc8181;"></span>&lt; 30%</span>
        <span><span class="legend-color" style="background-color: #f6ad55;"></span>30-70%</span>
        <span><span class="legend-color" style="background-color: #68d391;"></span>&gt; 70%</span>
        <span><span class="legend-color" style="background-color: #4299e1;"></span>100%</span>
    </div>

    <div class="footer">
        OJT Time Logging and Monitoring System | Progress Report | {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
