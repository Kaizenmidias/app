<aside class="scraper-shell-sidebar prospecting-shell-sidebar" aria-label="NavegaÃ§Ã£o principal">
<a class="scraper-shell-brand" href="{{ route('dashboard') }}"><img src="{{ asset('app/img/logo.png') }}" alt="" width="34" height="34"><span><strong>Parceiros Kaizen</strong><small>Kaizen MÃ­dias</small></span></a>
<nav class="scraper-shell-nav">
@foreach([['dashboard','â–¦','VisÃ£o geral'],['prospecting','âŒ–','ProspecÃ§Ã£o'],['dashboard#sec-jobs','â˜·','Suas buscas'],['leads','â™™','Leads'],['pipeline','â–¥','Pipeline'],['module.chats','â—Œ','Chats'],['module.numeros-conectados','â—‰','NÃºmeros conectados'],['module.disparar','âž¤','Disparar'],['module.email-marketing','âœ‰','E-mail Marketing'],['module.automacoes','âš™','AutomaÃ§Ãµes'],['module.agenda','â–£','Agenda'],['module.briefings','â–¤','Briefings'],['account.edit','â—¯','Minha conta']] as [$target,$icon,$label])
@php($href=str_contains($target,'#')?route('dashboard').'#sec-jobs':route($target))
<a href="{{ $href }}" @class(['is-active'=>request()->routeIs($target)||($target==='prospecting'&&request()->routeIs('prospecting'))])>{{ $icon }} <span>{{ $label }}</span></a>
@endforeach
</nav>
<div class="scraper-shell-footer"><span class="scraper-shell-muted-link">Kaizen MÃ­dias Â· Google Maps Scraper</span><span class="scraper-shell-account">{{ auth()->user()->email }}</span><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="scraper-shell-logout">Sair</button></form></div>
</aside>
