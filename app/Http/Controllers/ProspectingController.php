<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProspectingController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = file_get_contents(resource_path('views/prospecting.html'));
        $token = e(csrf_token());
        $shell = <<<HTML
<aside class="scraper-shell-sidebar prospecting-shell-sidebar" aria-label="Navegação principal">
 <a class="scraper-shell-brand" href="/dashboard"><img src="/app/img/logo.png" alt="" width="34" height="34"><span><strong>Parceiros Kaizen</strong><small>Kaizen Mídias</small></span></a>
 <nav class="scraper-shell-nav"><a href="/dashboard">▦ <span>Visão geral</span></a><a class="is-active" href="/prospecting">⌖ <span>Prospecção</span></a><a href="/dashboard#sec-jobs">☷ <span>Suas buscas</span></a><a href="/leads">♙ <span>Leads</span></a><a href="/pipeline">▥ <span>Pipeline</span></a><a href="/chats">◌ <span>Chats</span></a><a href="/numeros-conectados">◉ <span>Números conectados</span></a><a href="/disparar">➤ <span>Disparar</span></a><a href="/email-marketing">✉ <span>E-mail Marketing</span></a><a href="/automacoes">⚙ <span>Automações</span></a><a href="/agenda">▣ <span>Agenda</span></a><a href="/briefings">▤ <span>Briefings</span></a><a href="/minha-conta">◯ <span>Minha conta</span></a></nav>
 <div class="scraper-shell-footer"><span class="scraper-shell-muted-link">Kaizen Mídias · Google Maps Scraper</span><form method="post" action="/logout"><input type="hidden" name="_token" value="{$token}"><button type="submit" class="scraper-shell-logout">Sair</button></form></div>
</aside>
HTML;
        $html = str_replace('<body>', '<body class="prospecting-embedded">'.$shell, $html);
        $html = str_replace('</head>', '<link rel="stylesheet" href="/scraper-shell.css"><link rel="stylesheet" href="/prospecting-shell.css"></head>', $html);
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')
            ->cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax');
    }
}
