<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #fff; color: #1a1a1a; }
        .page { width: 100%; min-height: 100vh; padding: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 8px double #1a3a6b; }
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 28px; font-weight: bold; color: #1a3a6b; letter-spacing: 2px; text-transform: uppercase; }
        .school-address { font-size: 12px; color: #555; margin-top: 4px; }
        .divider { width: 80%; height: 2px; background: linear-gradient(to right, transparent, #1a3a6b, transparent); margin: 20px auto; }
        .cert-title { font-size: 36px; font-weight: bold; color: #1a3a6b; text-align: center; letter-spacing: 4px; text-transform: uppercase; margin: 20px 0; }
        .cert-type { font-size: 18px; color: #555; text-align: center; margin-bottom: 30px; font-style: italic; }
        .body-text { font-size: 15px; line-height: 2; text-align: center; max-width: 600px; margin: 0 auto 30px; }
        .student-name { font-size: 28px; font-weight: bold; color: #1a3a6b; border-bottom: 2px solid #1a3a6b; display: inline-block; padding: 0 20px; margin: 10px 0; }
        .details { display: flex; justify-content: space-around; width: 100%; margin: 30px 0; }
        .detail-item { text-align: center; }
        .detail-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .detail-value { font-size: 14px; font-weight: bold; color: #1a1a1a; margin-top: 4px; }
        .footer { display: flex; justify-content: space-between; width: 100%; margin-top: 50px; align-items: flex-end; }
        .signature-block { text-align: center; }
        .signature-line { width: 150px; border-top: 1px solid #1a1a1a; margin: 0 auto 5px; }
        .signature-label { font-size: 11px; color: #555; }
        .cert-number { font-size: 11px; color: #888; text-align: center; margin-top: 20px; }
        .purpose-text { font-size: 13px; color: #555; text-align: center; font-style: italic; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="school-name">{{ $institution->name ?? 'School Name' }}</div>
            <div class="school-address">{{ $institution->address ?? '' }}</div>
        </div>

        <div class="divider"></div>

        <div class="cert-title">Certificate</div>
        <div class="cert-type">{{ ucfirst(str_replace('_', ' ', $certificate->type)) }} Certificate</div>

        <div class="body-text">
            This is to certify that
        </div>

        <div class="student-name">
            {{ $student->user?->first_name }} {{ $student->user?->last_name }}
        </div>

        <div class="body-text" style="margin-top: 15px;">
            @if($certificate->content)
                {!! nl2br(e($certificate->content)) !!}
            @else
                is a bonafide student of this institution, currently enrolled in
                <strong>{{ $student->class?->name ?? '-' }}</strong>.
            @endif
        </div>

        @if($certificate->purpose)
            <div class="purpose-text">Purpose: {{ $certificate->purpose }}</div>
        @endif

        <div class="details">
            <div class="detail-item">
                <div class="detail-label">Admission No.</div>
                <div class="detail-value">{{ $student->admission_number ?? '-' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Class</div>
                <div class="detail-value">{{ $student->class?->name ?? '-' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Issue Date</div>
                <div class="detail-value">{{ $certificate->issue_date?->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Class Teacher</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">Principal / Head of School</div>
            </div>
        </div>

        <div class="cert-number">Certificate No: {{ $certificate->certificate_number }}</div>
    </div>
</body>
</html>
