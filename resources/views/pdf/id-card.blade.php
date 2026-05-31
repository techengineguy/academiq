<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { width: 320px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .card-header { background: linear-gradient(135deg, #1a3a6b, #2563eb); padding: 16px; text-align: center; color: white; }
        .school-name { font-size: 14px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .card-type { font-size: 10px; opacity: 0.8; margin-top: 2px; text-transform: uppercase; letter-spacing: 2px; }
        .card-body { padding: 20px; display: flex; gap: 16px; align-items: flex-start; }
        .avatar { width: 70px; height: 70px; border-radius: 8px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: #1a3a6b; flex-shrink: 0; border: 2px solid #1a3a6b; }
        .info { flex: 1; }
        .name { font-size: 16px; font-weight: bold; color: #1a1a1a; }
        .role { font-size: 11px; color: #2563eb; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }
        .detail { font-size: 11px; color: #555; margin-top: 4px; }
        .detail span { font-weight: 600; color: #1a1a1a; }
        .card-footer { background: #f8fafc; border-top: 1px solid #e5e7eb; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-number { font-size: 10px; color: #888; font-family: monospace; }
        .validity { font-size: 10px; color: #888; }
        .validity span { color: #1a3a6b; font-weight: 600; }
        .barcode { text-align: center; padding: 8px 20px; border-top: 1px solid #e5e7eb; }
        .barcode-text { font-family: monospace; font-size: 9px; color: #888; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="school-name">{{ $institution->name ?? 'School Name' }}</div>
            <div class="card-type">{{ ucfirst($idCard->type) }} ID Card</div>
        </div>

        <div class="card-body">
            <div class="avatar">
                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}
            </div>
            <div class="info">
                <div class="name">{{ $user->first_name }} {{ $user->last_name }}</div>
                <div class="role">{{ ucfirst($user->role) }}</div>

                @if($idCard->type === 'student' && $user->student)
                    <div class="detail">Class: <span>{{ $user->student->class?->name ?? '-' }}</span></div>
                    <div class="detail">Adm No: <span>{{ $user->student->admission_number ?? '-' }}</span></div>
                @elseif($idCard->type === 'teacher' && $user->teacher)
                    <div class="detail">Dept: <span>{{ $user->teacher->department ?? '-' }}</span></div>
                    <div class="detail">Emp ID: <span>{{ $user->teacher->employee_id ?? '-' }}</span></div>
                @elseif($idCard->type === 'staff' && $user->staff)
                    <div class="detail">Dept: <span>{{ $user->staff->department ?? '-' }}</span></div>
                    <div class="detail">Emp ID: <span>{{ $user->staff->employee_id ?? '-' }}</span></div>
                @endif

                <div class="detail">Phone: <span>{{ $user->phone ?? '-' }}</span></div>
            </div>
        </div>

        <div class="card-footer">
            <div class="card-number">{{ $idCard->card_number }}</div>
            <div class="validity">
                Valid till: <span>{{ $idCard->expiry_date?->format('M Y') ?? 'N/A' }}</span>
            </div>
        </div>

        @if($idCard->barcode)
            <div class="barcode">
                <div class="barcode-text">{{ $idCard->barcode }}</div>
            </div>
        @endif
    </div>
</body>
</html>
