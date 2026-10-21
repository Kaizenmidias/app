<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Parceiros Kaizen')</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="{{ asset('design-system/tokens.css') }}"><link rel="stylesheet" href="{{ asset('scraper-shell.css') }}?v=158">@stack('styles')
</head>
<body class="scraper-shell-page">
<button class="scraper-shell-menu-button" type="button" aria-expanded="false" aria-controls="scraper-sidebar" aria-label="Abrir menu"><span class="scraper-shell-menu-icon" aria-hidden="true"></span></button><div class="scraper-shell-menu-backdrop" hidden></div><x-scraper-sidebar />
<main class="scraper-shell-main">@yield('content')</main>@stack('scripts')
<script>
(() => { const sidebar=document.getElementById('scraper-sidebar'), button=document.querySelector('.scraper-shell-menu-button'), backdrop=document.querySelector('.scraper-shell-menu-backdrop'); if(!sidebar||!button||!backdrop)return; const close=()=>{sidebar.classList.remove('is-open');backdrop.hidden=true;button.setAttribute('aria-expanded','false');document.body.classList.remove('menu-open');button.focus();}; const open=()=>{backdrop.hidden=false;sidebar.classList.add('is-open');button.setAttribute('aria-expanded','true');document.body.classList.add('menu-open');sidebar.querySelector('a')?.focus();}; button.addEventListener('click',()=>sidebar.classList.contains('is-open')?close():open()); backdrop.addEventListener('click',close); document.addEventListener('keydown',e=>{if(e.key==='Escape'&&sidebar.classList.contains('is-open'))close();}); sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{if(window.matchMedia('(max-width: 900px)').matches)close();})); })();
</script></body></html>
