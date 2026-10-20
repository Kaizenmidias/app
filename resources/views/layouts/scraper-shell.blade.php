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
    <aside class="scraper-shell-sidebar" aria-label="NavegaÃ§Ã£o principal">
        <a class="scraper-shell-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('app/img/logo.png') }}" alt="" width="34" height="34">
            <span><strong>Parceiros Kaizen</strong><small>Kaizen Mídias</small></span>
        </a>

        <nav class="scraper-shell-nav">
            <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard')])>▦ <span>Visão geral</span></a>
            <a href="{{ route('prospecting') }}" @class(['is-active' => request()->routeIs('prospecting')])>⌖ <span>Prospecção</span></a>
            <a href="{{ route('dashboard') }}#sec-jobs">☷ <span>Suas buscas</span></a>
            <a href="{{ route('leads') }}" @class(['is-active' => request()->routeIs('leads')])>♙ <span>Leads</span></a>
            <a href="{{ route('pipeline') }}" @class(['is-active' => request()->routeIs('pipeline*')])>▥ <span>Pipeline</span></a>
            <a href="{{ route('module.chats') }}" @class(['is-active' => request()->routeIs('module.chats')])>◌ <span>Chats</span></a>
            <a href="{{ route('module.numeros-conectados') }}">◉ <span>Números conectados</span></a>
            <a href="{{ route('module.disparar') }}">➤ <span>Disparar</span></a>
            <a href="{{ route('module.email-marketing') }}">✉ <span>E-mail Marketing</span></a>
            <a href="{{ route('module.automacoes') }}">⚙ <span>Automações</span></a>
            <a href="{{ route('module.agenda') }}">▣ <span>Agenda</span></a>
            <a href="{{ route('module.briefings') }}">▤ <span>Briefings</span></a>
            <a href="{{ route('account.edit') }}" @class(['is-active' => request()->routeIs('account.*')])>◯ <span>Minha conta</span></a>
        </nav>
        <div class="scraper-shell-footer">
            <span class="scraper-shell-muted-link">Kaizen Mídias · Google Maps Scraper</span>
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
