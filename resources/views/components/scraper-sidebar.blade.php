<aside class="scraper-shell-sidebar prospecting-shell-sidebar" aria-label="Navegação principal">
<a class="scraper-shell-brand" href="{{ route('dashboard') }}"><img src="{{ asset('app/img/logo.png') }}" alt="" width="34" height="34"><span><strong>Parceiros Kaizen</strong><small>Kaizen Mídias</small></span></a>
<nav class="scraper-shell-nav">
@foreach([
['dashboard','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>','Visão geral'],
['prospecting','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>','Prospecção'],
['dashboard#sec-jobs','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14M5 12h14M5 18h14"/><circle cx="8" cy="6" r="1"/><circle cx="8" cy="12" r="1"/><circle cx="8" cy="18" r="1"/></svg>','Suas buscas'],
['leads','<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M16 11a3 3 0 1 1 2.5 1.3M16 16a5 5 0 0 1 4.5 3"/></svg>','Leads'],
['pipeline','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="6" height="14" rx="1"/><rect x="14" y="5" width="6" height="14" rx="1"/><path d="M10 12h4"/></svg>','Pipeline'],
['module.chats','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6.5A3.5 3.5 0 0 1 8.5 3h7A3.5 3.5 0 0 1 19 6.5v5a3.5 3.5 0 0 1-3.5 3.5H11l-4.5 4v-4.4A3.5 3.5 0 0 1 5 11.5Z"/></svg>','Chats'],
['module.numeros-conectados','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M10 6h4M11 18h2"/></svg>','Números conectados'],
['module.disparar','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 4 16 8-16 8 3-8-3-8Z"/><path d="M7 12h13"/></svg>','Disparar'],
['module.email-marketing','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="6" width="16" height="12" rx="2"/><path d="m5 8 7 5 7-5"/></svg>','E-mail Marketing'],
['module.automacoes','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8"/><circle cx="12" cy="12" r="3"/></svg>','Automações'],
['module.agenda','<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>','Agenda'],
['module.briefings','<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h12v16H6zM9 8h6M9 12h6M9 16h4"/></svg>','Briefings'],
['account.edit','<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>','Minha conta']
] as [$target,$icon,$label])
@php($href=str_contains($target,'#')?route('dashboard').'#sec-jobs':route($target))
<a href="{{ $href }}" @class(['is-active'=>request()->routeIs($target)||($target==='prospecting'&&request()->routeIs('prospecting'))])>{!! $icon !!}<span>{{ $label }}</span></a>
@endforeach
</nav>
<div class="scraper-shell-footer"><span class="scraper-shell-muted-link">Kaizen Mídias · Google Maps Scraper</span><span class="scraper-shell-account">{{ auth()->user()->email }}</span><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="scraper-shell-logout">Sair</button></form></div>
</aside>
