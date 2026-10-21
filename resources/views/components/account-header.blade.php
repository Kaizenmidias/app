@php($firstName = trim((string) optional(auth()->user())->name))
@php($firstName = $firstName !== '' ? preg_split('/\s+/', $firstName)[0] : 'Parceiro')
<header class="scraper-shell-header" aria-label="Conta autenticada">
    <p>Bem-vindo, <strong>{{ $firstName }}</strong></p>
    <span class="scraper-shell-avatar" aria-label="Avatar de {{ auth()->user()->name ?: 'Parceiro' }}">{{ mb_strtoupper(mb_substr($firstName, 0, 1)) }}</span>
</header>
