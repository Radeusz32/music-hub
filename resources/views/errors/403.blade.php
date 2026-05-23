<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>403 — Brak dostępu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #070b14;
            color: rgba(241, 245, 249, 0.92);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .glow {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
        }
        .glow-1 {
            top: -200px; left: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, #ef4444 0%, transparent 70%);
            opacity: 0.05;
        }
        .glow-2 {
            bottom: -200px; right: -200px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, #6366f1 0%, transparent 70%);
            opacity: 0.07;
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 480px;
            background: #0d1424;
            border: 1px solid rgba(248, 113, 113, 0.12);
            border-radius: 1.25rem;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(248, 113, 113, 0.06);
            padding: 3rem 2.5rem;
            text-align: center;
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 1.25rem;
            background: rgba(248, 113, 113, 0.08);
            border: 1px solid rgba(248, 113, 113, 0.18);
            margin-bottom: 1.75rem;
        }

        .icon-wrap svg {
            width: 2.25rem;
            height: 2.25rem;
            color: #f87171;
        }

        .code {
            font-size: 5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #f87171 0%, #fb923c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgba(241, 245, 249, 0.92);
            margin-bottom: 0.75rem;
        }

        .description {
            font-size: 0.9rem;
            color: rgba(148, 163, 184, 0.72);
            line-height: 1.65;
            margin-bottom: 2rem;
        }

        .divider {
            height: 1px;
            background: rgba(248, 113, 113, 0.07);
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(241, 245, 249, 0.85);
            border: 1px solid rgba(56, 189, 248, 0.15);
            box-shadow: none;
        }

        .btn:hover {
            background: rgba(56, 189, 248, 0.08);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
        }

        .btn svg {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z" />
            </svg>
        </div>

        <div class="code">403</div>
        <div class="title">Brak uprawnień</div>
        <p class="description">
            Nie masz uprawnień do wyświetlenia tej strony.<br>
            Jeśli uważasz, że to błąd, skontaktuj się z administratorem.
        </p>

        <div class="divider"></div>

        <a href="{{ url('/') }}" class="btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Wróć do panelu
        </a>
    </div>
</body>
</html>
