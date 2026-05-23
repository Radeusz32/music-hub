<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>404 — Nie znaleziono strony</title>
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
            background: radial-gradient(circle, #0ea5e9 0%, transparent 70%);
            opacity: 0.08;
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
            border: 1px solid rgba(56, 189, 248, 0.12);
            border-radius: 1.25rem;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(56, 189, 248, 0.06);
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
            background: rgba(56, 189, 248, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.15);
            margin-bottom: 1.75rem;
        }

        .icon-wrap svg {
            width: 2.25rem;
            height: 2.25rem;
            color: #38bdf8;
        }

        .code {
            font-size: 5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #38bdf8 0%, #6366f1 100%);
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
            background: rgba(56, 189, 248, 0.07);
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
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            color: #fff;
            border: none;
            box-shadow: 0 0 24px rgba(14, 165, 233, 0.2);
        }

        .btn:hover {
            opacity: 0.88;
            box-shadow: 0 0 32px rgba(14, 165, 233, 0.35);
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
            </svg>
        </div>

        <div class="code">404</div>
        <div class="title">Strona nie została znaleziona</div>
        <p class="description">
            Strona, której szukasz, nie istnieje lub została przeniesiona.<br>
            Sprawdź adres URL lub wróć do panelu głównego.
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
