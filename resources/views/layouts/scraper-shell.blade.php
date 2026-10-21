<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parceiros Kaizen')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('design-system/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('scraper-shell.css') }}">
    @stack('styles')
</head>
<body class="scraper-shell-page">
    <x-scraper-sidebar />

    <main class="scraper-shell-main">
        @php($firstName = trim((string) optional(auth()->user())->name))
        @php($firstName = $firstName !== '' ? preg_split('/\\s+/', $firstName)[0] : 'Parceiro')
        <header class="scraper-shell-header">
            <p>Bem-vindo, <strong>{{ $firstName }}</strong></p>
            <span class="scraper-shell-avatar" aria-label="Avatar de {{ auth()->user()->name ?: 'Parceiro' }}">{{ mb_strtoupper(mb_substr($firstName, 0, 1)) }}</span>
        </header>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
