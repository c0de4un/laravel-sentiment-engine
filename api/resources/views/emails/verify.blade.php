<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .link-fallback {
            word-break: break-all;
            background-color: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">🎭 Sentiment Analyzer</div>
    </div>

    <h2>Здравствуйте, {{ $user->name }}!</h2>

    <p>Спасибо за регистрацию в <strong>Sentiment Analyzer</strong> — сервисе для анализа тональности текста с помощью AI.</p>

    <p>Для завершения регистрации и получения доступа к анализу комментариев, пожалуйста, подтвердите ваш email:</p>

    <div style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">Подтвердить email</a>
    </div>

    <p style="font-size: 14px; color: #666;">
        Если кнопка не работает, скопируйте и вставьте эту ссылку в браузер:
    </p>
    <div class="link-fallback">
        {{ $verificationUrl }}
    </div>

    <p style="font-size: 14px; margin-top: 20px;">
        <strong>Ссылка действительна 60 минут.</strong>
    </p>

    <div class="footer">
        <p>Если вы не регистрировались в Sentiment Analyzer, просто проигнорируйте это письмо.</p>
        <p>© {{ date('Y') }} Sentiment Analyzer. Все права защищены.</p>
    </div>
</div>
</body>
</html>
