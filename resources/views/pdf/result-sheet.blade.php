<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 30px; }

        /* Header */
        .header { text-align: center; border-bottom: 3px double #1a3a6b; padding-bottom: 14px; margin-bottom: 16px; }
        .school-name { font-size: 22px; font-weight: bold; color: #1a3a6b; letter-spacing: 1px; text-transform: uppercase; }
        .school-sub { font-size: 11px; color: #555; margin-top: 3px; }
        .report-title { font-size: 16px; font-weight: bold; color: #1a3a6b; margin-top: 10px; letter-spacing: 2px; text-transform: uppercase; }
        .exam-name { font-size: 13px; color: #444; margin-top: 3px; }

        /* Student info */
        .student-info { display: flex; justify-content: space-between; background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 12px 16px; margin-bottom: 18px; }
        .info-group { display: flex; flex-direction: column; gap: 4px; }
        .info-row { display: flex; gap: 6px; font-size: 11px; }
        .info-label { color: #666; min-width: 90px; }
        .info-value { font-weight: bold; color: #1a1a1a; }

        /* Results table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1a3a6b; color: white; }
        thead th { padding: 9px 10px; text-align: left; font-size: 11px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f8faff; }
        tbody tr:hover { background: #eef2ff; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        tbody td.center { text-align: center; }
        .absent-row td { color: #9ca3af; font-style: italic; }
        .pass { color: #16a34a; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .grade-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; background: #dbeafe; color: #1d4ed8; }

        /* Summary */
        .summary-section { display: flex; gap: 16px; margin-bottom: 20px; }
        .summary-card { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; text-align: center; }
        .summary-card.highlight { background: #1a3a6b; color: white; border-color: #1a3a6b; }
        .summary-card.pass-card { background: #f0fdf4; border-color: #86efac; }
        .summary-card.fail-card { background: #fef2f2; border-color: #fca5a5; }
        .summary-value { font-size: 22px; font-weight: bold; }
        .summary-label { font-size: 10px; color: inherit; opacity: 0.75; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card.highlight .summary-label { color: #c7d2fe; }

        /* Progress bar */
        .progress-bar { width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; margin-top: 6px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; }

        /* Remarks */
        .remarks-section { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
        .remarks-title { font-size: 11px; font-weight: bold; color: #1a3a6b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .remarks-text { font-size: 12px; color: #444; line-height: 1.6; }

        /* Result banner */
        .result-banner { text-align: center; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 18px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .result-banner.pass { background: #f0fdf4; color: #16a34a; border: 2px solid #86efac; }
        .result-banner.fail { background: #fef2f2; color: #dc2626; border: 2px solid #fca5a5; }

        /* Signatures */
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .sig-block { text-align: center; }
        .sig-line { width: 140px; border-top: 1px solid #1a1a1a; margin: 0 auto 5px; }
        .sig-label { font-size: 10px; color: #555; }

        /* Footer */
        .footer { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; font-weight: bold; color: rgba(26, 58, 107, 0.04); pointer-events: none; white-space: nowrap; z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">{{ $institution->name ?? 'SCHOOL' }}</div>

    {{-- Header --}}
    <div class="header">
        <div class="school-name">{{ $institution->name ?? 'School Name' }}</div>
        <div class="school-sub">{{ $institution->address ?? '' }}</div>
        <div class="report-title">Academic Result Sheet</div>
        <div class="exam-name">{{ $exam->name }} &mdash; {{ $exam->start_date?->format('Y') }}</div>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="info-group">
            <div class="info-row">
                <span class="info-label">Student Name:</span>
                <span class="info-value">{{ $student->user?->first_name }} {{ $student->user?->last_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Admission No.:</span>
                <span class="info-value">{{ $student->admission_number ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Roll No.:</span>
                <span class="info-value">{{ $student->roll_number ?? '-' }}</span>
            </div>
        </div>
        <div class="info-group">
            <div class="info-row">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ $student->class?->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Section:</span>
                <span class="info-value">{{ $student->section?->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Academic Year:</span>
                <span class="info-value">{{ $exam->academicYear?->name ?? '-' }}</span>
            </div>
        </div>
        <div class="info-group">
            <div class="info-row">
                <span class="info-label">Exam Type:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $exam->type)) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Issue Date:</span>
                <span class="info-value">{{ now()->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th class="center">Total Marks</th>
                <th class="center">Marks Obtained</th>
                <th class="center">Percentage</th>
                <th class="center">Grade</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $i => $result)
                @php
                    $pct = $result->total_marks > 0 ? round(($result->marks_obtained / $result->total_marks) * 100, 1) : 0;
                    $passed = ! $result->is_absent && (float) $result->marks_obtained >= (float) ($result->examSchedule?->passing_marks ?? 0);
                @endphp
                <tr class="{{ $result->is_absent ? 'absent-row' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $result->examSchedule?->subject?->name ?? '-' }}</strong></td>
                    <td class="center">{{ $result->total_marks }}</td>
                    <td class="center">
                        @if($result->is_absent)
                            <em>Absent</em>
                        @else
                            {{ $result->marks_obtained }}
                        @endif
                    </td>
                    <td class="center">
                        @if($result->is_absent) - @else {{ $pct }}% @endif
                    </td>
                    <td class="center">
                        @if($result->grade && ! $result->is_absent)
                            <span class="grade-badge">{{ $result->grade }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="center">
                        @if($result->is_absent)
                            <span style="color:#9ca3af">Absent</span>
                        @elseif($passed)
                            <span class="pass">Pass</span>
                        @else
                            <span class="fail">Fail</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Cards --}}
    @php
        $totalMarks = $results->sum('total_marks');
        $marksObtained = $results->where('is_absent', false)->sum('marks_obtained');
        $overallPct = $totalMarks > 0 ? round(($marksObtained / $totalMarks) * 100, 2) : 0;
        $passedCount = $results->filter(fn($r) => !$r->is_absent && (float)$r->marks_obtained >= (float)($r->examSchedule?->passing_marks ?? 0))->count();
        $failedCount = $results->filter(fn($r) => !$r->is_absent && (float)$r->marks_obtained < (float)($r->examSchedule?->passing_marks ?? 0))->count();
        $absentCount = $results->where('is_absent', true)->count();
        $overallPass = $failedCount === 0 && $absentCount < $results->count();
        $progressColor = $overallPct >= 75 ? '#16a34a' : ($overallPct >= 50 ? '#d97706' : '#dc2626');
    @endphp

    <div class="result-banner {{ $overallPass ? 'pass' : 'fail' }}">
        {{ $overallPass ? 'RESULT: PASS' : 'RESULT: FAIL' }}
    </div>

    <div class="summary-section">
        <div class="summary-card highlight">
            <div class="summary-value">{{ $marksObtained }} / {{ $totalMarks }}</div>
            <div class="summary-label">Total Marks</div>
        </div>
        <div class="summary-card">
            <div class="summary-value" style="color: {{ $progressColor }}">{{ $overallPct }}%</div>
            <div class="summary-label">Overall Percentage</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $overallPct }}%; background: {{ $progressColor }};"></div>
            </div>
        </div>
        <div class="summary-card pass-card">
            <div class="summary-value" style="color: #16a34a">{{ $passedCount }}</div>
            <div class="summary-label">Subjects Passed</div>
        </div>
        <div class="summary-card fail-card">
            <div class="summary-value" style="color: #dc2626">{{ $failedCount }}</div>
            <div class="summary-label">Subjects Failed</div>
        </div>
        @if($absentCount > 0)
            <div class="summary-card">
                <div class="summary-value" style="color: #9ca3af">{{ $absentCount }}</div>
                <div class="summary-label">Absent</div>
            </div>
        @endif
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Class Teacher</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Examination Controller</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Principal</div>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated result sheet. &mdash; {{ $institution->name ?? '' }} &mdash; Printed on {{ now()->format('M d, Y H:i') }}
    </div>
</body>
</html>
