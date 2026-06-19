<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizacja gotowa - MusicHub</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0f1e; color: #e2e8f0; margin: 0; padding: 40px 20px; }
        .card { max-width: 560px; margin: 0 auto; background: #0d1428; border: 1px solid rgba(56,189,248,0.15); border-radius: 16px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #07261a 0%, #0c0818 100%); padding: 32px; text-align: center; border-bottom: 1px solid rgba(74,222,128,0.2); }
        .logo { font-size: 22px; font-weight: 700; color: #d946ef; letter-spacing: 0.05em; }
        .check { font-size: 36px; margin-bottom: 8px; }
        .body { padding: 32px; }
        h1 { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0 0 12px; }
        p { font-size: 14px; color: #94a3b8; line-height: 1.7; margin: 0 0 20px; }
        .btn { display: block; width: fit-content; margin: 24px auto; padding: 14px 32px; background: linear-gradient(135deg, #4ade80, #22d3ee); border-radius: 10px; color: #04210f; text-decoration: none; font-weight: 700; font-size: 15px; }
        .info { background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.18); border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #94a3b8; text-align: center; margin-top: 24px; }
        .footer { padding: 20px 32px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #475569; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="check">✅</div>
            <div class="logo">MusicHub</div>
        </div>
        <div class="body">
            <h1>Organizacja została utworzona!</h1>
            <p>Cześć {{ $ownerName }},</p>
            <p>Twoja organizacja <strong style="color: #e2e8f0">{{ $companyName }}</strong> została pomyślnie utworzona na platformie MusicHub. Możesz już zalogować się i rozpocząć pracę.</p>
            <a href="{{ $loginUrl }}" class="btn">Zaloguj się</a>
            <div class="info">
                🔐 Logujesz się danymi ustawionymi podczas rejestracji.
            </div>
            <p style="margin-top: 20px; font-size: 12px">Jeśli przycisk nie działa, skopiuj poniższy adres do przeglądarki:<br>
                <span style="color: #38bdf8; word-break: break-all">{{ $loginUrl }}</span>
            </p>
        </div>
        <div class="footer">
            MusicHub · Wiadomość wysłana automatycznie - nie odpowiadaj na tego e-maila.
        </div>
    </div>
</body>
</html>
