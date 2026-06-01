<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Error' }} — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: {{ $accent ?? '#485AE0' }};
            --accent-light: {{ $accentLight ?? '#eef0fd' }};
        }

        html { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

        body {
            min-height: 100vh;
            background: #0f0f13;
            color: #e4e4e7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
            position: relative;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* Glowing orb */
        body::after {
            content: '';
            position: fixed;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, {{ $glow ?? 'rgba(72,90,224,0.15)' }} 0%, transparent 70%);
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        /* Giant error code */
        .error-code {
            font-size: clamp(7rem, 20vw, 11rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, var(--accent) 0%, {{ $codeGradient ?? '#818cf8' }} 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            user-select: none;
        }

        .divider {
            width: 48px;
            height: 3px;
            background: var(--accent);
            border-radius: 99px;
            margin: 1.25rem auto;
            opacity: 0.6;
        }

        .title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f4f4f5;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .message {
            font-size: 0.9375rem;
            color: #71717a;
            line-height: 1.7;
            max-width: 380px;
            margin: 0 auto 2.5rem;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: none;
            outline: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }

        .btn-ghost {
            background: rgba(255,255,255,0.06);
            color: #a1a1aa;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); color: #e4e4e7; transform: translateY(-1px); }

        .footer-text {
            margin-top: 3rem;
            font-size: 0.75rem;
            color: #3f3f46;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        svg { flex-shrink: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">{{ $code }}</div>

        <div class="divider"></div>

        <h1 class="title">{{ $title }}</h1>
        <p class="message">{{ $message }}</p>

        <div class="actions">
            @if(auth()->check())
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Dashboard
                </a>
            @endif
            <a href="javascript:history.back()" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Go Back
            </a>
        </div>

        <p class="footer-text">{{ config('app.name') }}</p>
    </div>
</body>
</html>
