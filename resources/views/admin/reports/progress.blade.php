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
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #2d3748;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #1a202c;
            letter-spacing: 1px;
        }
        .header p {
            color: #718096;
            margin: 0;
            font-size: 8px;
        }
        .summary {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 8px 0 10px 0;
        }
        .summary-card {
            flex: 1;
            text-align: center;
            padding: 10px 8px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            min-width: 80px;
            position: relative;
            overflow: hidden;
        }
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        .summary-card.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .summary-card.blue::before { background: #3182ce; }
        .summary-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .summary-card.green::before { background: #38a169; }
        .summary-card.purple { background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%); }
        .summary-card.purple::before { background: #805ad5; }
        .summary-card.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .summary-card.orange::before { background: #dd6b20; }
        .summary-card.red { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .summary-card.red::before { background: #e53e3e; }

        .summary-card .card-inner {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 4px;
            padding: 8px;
            position: relative;
            z-index: 1;
        }
        .summary-card .number {
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            line-height: 1;
            display: block;
        }
        .summary-card .label {
            font-size: 7px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            display: block;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #2d3748;
            padding: 5px 4px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        tr:hover {
            background-color: #edf2f7;
        }
        .col-id { width: 25px; text-align: center; }
        .col-student-id { width: 60px; }
        .col-name { width: 110px; }
        .col-dept { width: 90px; }
        .col-days { width: 30px; text-align: center; }
        .col-hours { width: 40px; text-align: center; }
        .col-required { width: 35px; text-align: center; }
        .col-remaining { width: 40px; text-align: center; }
        .col-progress { width: 90px; }
        .col-est { width: 65px; }
        .col-status { width: 55px; text-align: center; }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .progress-bar-bg {
            background-color: #e2e8f0;
            height: 10px;
            border-radius: 5px;
            flex: 1;
            min-width: 35px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .progress-fill.low {
            background: linear-gradient(90deg, #fc8181 0%, #e53e3e 100%);
        }
        .progress-fill.medium {
            background: linear-gradient(90deg, #f6ad55 0%, #dd6b20 100%);
        }
        .progress-fill.high {
            background: linear-gradient(90deg, #68d391 0%, #38a169 100%);
        }
        .progress-fill.complete {
            background: linear-gradient(90deg, #63b3ed 0%, #3182ce 100%);
        }
        .percentage-text {
            font-weight: bold;
            font-size: 7px;
            min-width: 30px;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .status-completed {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: #2a4365;
            border: 1px solid #90cdf4;
        }
        .status-inactive {
            background: linear-gradient(135deg, #fed7d7 0%, #fc8181 100%);
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        .status-pending {
            background: linear-gradient(135deg, #fefcbf 0%, #fbd38d 100%);
            color: #744210;
            border: 1px solid #fbd38d;
        }
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7px;
            color: #718096;
        }
        .legend {
            margin-top: 6px;
            padding: 6px 10px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 7px;
        }
        .legend span {
            display: inline-block;
            margin-right: 12px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 10px;
            margin-right: 3px;
            vertical-align: middle;
            border-radius: 3px;
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

    {{-- Summary Section - Card Style --}}
    @php
        $totalStudents = $students->count();
        $activeStudents = $students->where('status', 'active')->count();
        $completedStudents = $students->where('status', 'completed')->count();
        $totalHoursCompleted = $students->sum('total_hours');
        $avgPercentage = $totalStudents > 0 ? $students->sum('percentage') / $totalStudents : 0;
    @endphp

    <div class="summary">
        <div class="summary-card blue">
            <div class="card-inner">
                <span class="number">{{ $totalStudents }}</span>
                <span class="label">Total Students</span>
            </div>
        </div>
        <div class="summary-card green">
            <div class="card-inner">
                <span class="number">{{ $activeStudents }}</span>
                <span class="label">Active</span>
            </div>
        </div>
        <div class="summary-card purple">
            <div class="card-inner">
                <span class="number">{{ $completedStudents }}</span>
                <span class="label">Completed</span>
            </div>
        </div>
        <div class="summary-card orange">
            <div class="card-inner">
                <span class="number">{{ number_format($totalHoursCompleted) }}</span>
                <span class="label">Total Hours</span>
            </div>
        </div>
        <div class="summary-card red">
            <div class="card-inner">
                <span class="number">{{ number_format($avgPercentage, 1) }}%</span>
                <span class="label">Avg Progress</span>
            </div>
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
        <span><span class="legend-color" style="background: linear-gradient(90deg, #fc8181 0%, #e53e3e 100%);"></span>&lt; 30%</span>
        <span><span class="legend-color" style="background: linear-gradient(90deg, #f6ad55 0%, #dd6b20 100%);"></span>30-70%</span>
        <span><span class="legend-color" style="background: linear-gradient(90deg, #68d391 0%, #38a169 100%);"></span>&gt; 70%</span>
        <span><span class="legend-color" style="background: linear-gradient(90deg, #63b3ed 0%, #3182ce 100%);"></span>100%</span>
    </div>

    <div class="footer">
        OJT Time Logging and Monitoring System | Progress Report | {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
