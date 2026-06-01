<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accountant Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px; }
        .heading { font-size: 24px; font-weight: bold; color: #1a1a1a; margin-bottom: 8px; }
        .text { font-size: 16px; color: #4a4a4a; line-height: 1.6; margin-bottom: 16px; }
        .button { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin: 16px 0; }
        .footer { font-size: 13px; color: #9a9a9a; margin-top: 24px; border-top: 1px solid #eee; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="heading">You've been invited!</p>
        <p class="text">
            Hello {{ $invitation->first_name }},<br><br>
            You have been invited to join <strong>{{ $invitation->institution->name }}</strong> as an accountant.
        </p>
        <p class="text">
            Click the button below to accept your invitation and create your account. This invitation expires on
            <strong>{{ $invitation->expires_at->format('F j, Y') }}</strong>.
        </p>
        <a href="{{ route('accountant.invitation.accept', $invitation->token) }}" class="button">
            Accept Invitation
        </a>
        <p class="text">
            If you did not expect this invitation, you can safely ignore this email.
        </p>
        <div class="footer">
            If the button does not work, copy and paste this URL into your browser:<br>
            {{ route('accountant.invitation.accept', $invitation->token) }}
        </div>
    </div>
</body>
</html>
