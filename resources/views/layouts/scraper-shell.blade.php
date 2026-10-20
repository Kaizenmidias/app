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
    <aside class="scraper-shell-sidebar" aria-label="Navegação principal">
        <a class="scraper-shell-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('app/img/logo.png') }}" alt="" width="34" height="34">
            <span><strong>Google Maps Scraper</strong><small>Painel de coleta</small></span>
        </a>

        <div class="scraper-shell-mode" role="tablist" aria-label="Modo de visualização">
            <a href="{{ route('prospecting') }}" role="tab" @class(['is-active' => request()->routeIs('prospecting')])>Mapa</a>
            <a href="{{ route('dashboard') }}" role="tab" @class(['is-active' => request()->routeIs('dashboard')])>Painel</a>
        </div>

        <nav class="scraper-shell-nav">
            <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard')])>
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6.5" height="6.5" rx="1.8"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.8"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.8"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.8"/></svg>
                Visão geral
            </a>
            <a href="{{ route('prospecting') }}" @class(['is-active' => request()->routeIs('prospecting')])>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 8v8M8 12h8"/></svg>
                Nova busca
            </a>
            <a href="{{ route('dashboard') }}#sec-jobs">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6.5h11M9 12h11M9 17.5h11"/><circle cx="4.8" cy="6.5" r=".9"/><circle cx="4.8" cy="12" r=".9"/><circle cx="4.8" cy="17.5" r=".9"/></svg>
                Suas buscas
            </a>
            <a href="{{ route('leads') }}" @class(['is-active' => request()->routeIs('leads')])>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c.6-3.2 2.4-5 5.5-5s4.9 1.8 5.5 5M16 9h5M18.5 6.5v5"/></svg>
                Leads
            </a>
            <a href="{{ route('pipeline') }}" @class(['is-active' => request()->routeIs('pipeline*')])>
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="5" height="16" rx="1"/><rect x="10" y="7" width="5" height="13" rx="1"/><rect x="17" y="10" width="4" height="10" rx="1"/></svg>
                Pipeline
            </a>
        </nav>

        <div class="scraper-shell-footer">
            <a class="scraper-shell-muted-link" href="{{ route('painel') }}">Abrir painel original</a>
            <span class="scraper-shell-account">{{ auth()->user()->email }}</span>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="scraper-shell-logout">Sair</button>
            </form>
        </div>
    </aside>

    <main class="scraper-shell-main">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
