<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Parceiros Kaizen' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('app/img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('auth/login.css') }}">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-card" aria-label="Login Parceiros Kaizen">
            <header class="auth-brand">
                <img src="{{ asset('auth/logo-kaizen.png') }}" alt="Logo Kaizen Mídias" width="96" height="96">
                <h1>Parceiros Kaizen</h1>
            </header>
            @yield('content')
        </section>
    </main>
</body>
</html>
