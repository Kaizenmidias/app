@extends('layouts.scraper-shell')

@section('title', 'Leads · Parceiros Kaizen')

@push('styles')
<link rel="stylesheet" href="{{ asset('leads.css') }}?v={{ filemtime(public_path('leads.css')) }}">
@endpush

@section('content')
<header class="module-header">
    <div><p class="module-eyebrow">CRM</p><h1>{{ $resultJob ? 'Resultado da busca' : 'Leads' }}</h1><p class="module-description">{{ $resultJob ? 'Leads encontrados em '.($resultJob->search_parameters['search_terms'][0] ?? $resultJob->keywords[0] ?? 'sua busca').'.' : 'Gerencie os contatos pertencentes à sua conta.' }}</p></div>
    <div class="module-actions"><span class="module-count">{{ $leads->total() }} {{ $leads->total() === 1 ? 'lead' : 'leads' }}</span><button type="button" class="lead-button lead-button-primary" data-dialog-open="new-lead">+ Novo lead</button></div>
</header>

@if(session('status'))<p class="lead-status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="lead-errors">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@if($resultSummary)<section class="result-summary" aria-label="Resumo da busca"><div><span>Leads coletados</span><strong>{{ $resultSummary['collected'] }}</strong></div><div><span>Leads duplicados</span><strong>{{ $resultSummary['duplicates'] }}</strong></div><div><span>Leads sem site</span><strong>{{ $resultSummary['without_site'] }}</strong></div><div><span>WhatsApp validado</span><strong>{{ $resultSummary['whatsapp_validated'] }}</strong></div></section>@endif

<div class="leads-workspace">
    <aside class="lead-folders" aria-label="Listas de leads">
        <div class="lead-folders-head"><div><p class="module-eyebrow">Organização</p><h2>Listas</h2></div><button type="button" class="lead-icon-button" data-dialog-open="new-list" aria-label="Criar lista">+</button></div>
        <a class="lead-folder {{ !$activeListId ? 'is-active' : '' }}" href="{{ route('leads') }}"><span class="lead-folder-icon">▰</span><span>Todos os leads</span><b>{{ auth()->user()->leads()->count() }}</b></a>
        @foreach($lists as $list)
            <a class="lead-folder {{ $activeListId === $list->id ? 'is-active' : '' }}" href="{{ route('leads', ['list' => $list->id]) }}"><span class="lead-folder-icon">▰</span><span>{{ $list->name }}</span><b>{{ $list->leads_count }}</b></a>
        @endforeach
        @if($lists->isEmpty())<p class="lead-folders-empty">Crie uma lista para organizar seus contatos como pastas.</p>@endif
    </aside>

    <section class="leads-main">
        <div class="leads-toolbar">
            <form class="lead-search" method="get" action="{{ $resultJob ? route('leads.results', $resultJob) : route('leads') }}" role="search">
                @if($activeListId)<input type="hidden" name="list" value="{{ $activeListId }}">@endif
                <label class="sr-only" for="lead-search">Buscar leads</label><input id="lead-search" name="q" value="{{ $term }}" placeholder="Buscar por nome, e-mail, telefone ou endereço">
                <select name="contact" aria-label="Filtrar contatos"><option value="all">Todos os contatos</option><option value="email" @selected($contactFilter==='email')>Com e-mail</option><option value="phone" @selected($contactFilter==='phone')>Com telefone</option><option value="site" @selected($contactFilter==='site')>Com site</option><option value="without_site" @selected($contactFilter==='without_site')>Sem site</option></select>
                <select name="per_page" aria-label="Resultados por página">@foreach([20,30,50,100,150] as $size)<option value="{{ $size }}" @selected($perPage===$size)>{{ $size }} por página</option>@endforeach</select>
                <button type="submit" class="lead-button lead-button-primary">Filtrar</button>
            </form>
        </div>
        <div class="bulk-actions" id="bulk-actions"><button type="button" class="lead-button lead-button-secondary" data-dialog-open="new-label">Nova etiqueta</button><strong>Ações em massa</strong><form id="bulk-list-form" method="post" action="{{ route('leads.assign-list') }}">@csrf<div class="bulk-lead-ids"></div><select name="lead_list_id"><option value="">Sem lista</option>@foreach($lists as $list)<option value="{{ $list->id }}">{{ $list->name }}</option>@endforeach</select><button type="submit" class="lead-button lead-button-secondary" disabled>Aplicar lista</button></form><form id="bulk-pipeline-form" method="post" action="{{ route('pipeline.send-many') }}">@csrf<div class="bulk-lead-ids"></div><button type="submit" class="lead-button lead-button-secondary" id="bulk-pipeline" disabled>Enviar selecionados para a pipeline</button></form></div>

        @if($leads->isEmpty())
            <div class="lead-empty"><strong>Nenhum lead encontrado</strong><p>{{ $term ? 'Tente outro termo de busca.' : 'Os leads das suas buscas concluídas aparecerão aqui.' }}</p></div>
        @else
            <div class="lead-table-wrap">
                <table class="lead-table">
                    <thead><tr><th><input type="checkbox" id="select-all" aria-label="Selecionar todos"></th><th>Nome</th><th><span class="email-heading">E-mail <button type="button" id="copy-emails" title="Copiar e-mails exibidos ou selecionados" aria-label="Copiar e-mails">⧉</button></span></th><th>WhatsApp</th><th>Site</th><th>Instagram</th><th>Lista</th><th>Capturado em</th><th><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                    @foreach($leads as $lead)
                        <tr class="lead-row" data-dialog-open="lead-{{ $lead->id }}" tabindex="0">
                            <td><input class="lead-select" type="checkbox" value="{{ $lead->id }}" aria-label="Selecionar {{ $lead->name }}"></td>
                            <td><strong>{{ $lead->name ?: 'Empresa sem nome' }}</strong>@if($lead->category)<small>{{ $lead->category }}</small>@endif</td>
                            <td class="lead-email" data-email="{{ $lead->email }}">{{ $lead->email ?: 'Não informado' }}</td>
                            @php($phoneDigits = preg_replace('/\D+/', '', (string) $lead->phone))
                            @php($whatsappDigits = strlen($phoneDigits) >= 10 && strlen($phoneDigits) <= 11 ? '55'.$phoneDigits : $phoneDigits)
                            <td>@if(strlen($whatsappDigits) >= 12 && strlen($whatsappDigits) <= 13)<a class="lead-whatsapp" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">{{ $lead->phone }}</a>@else {{ $lead->phone ?: 'Não informado' }} @endif</td>
                            <td>@if($lead->website)<a href="{{ $lead->website }}" target="_blank" rel="noopener">Abrir site</a>@else Não informado @endif</td>
                            <td>@if($lead->instagram)<a href="{{ $lead->instagram }}" target="_blank" rel="noopener">Abrir Instagram</a>@else Não informado @endif</td>
                            <td>{{ $lead->list?->name ?: 'Sem lista' }}</td>
                            <td>{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="lead-actions-cell"><details class="lead-menu"><summary aria-label="Ações do lead">•••</summary><div><button type="button" data-dialog-open="lead-{{ $lead->id }}">Ver e editar</button><button type="button" data-dialog-open="label-{{ $lead->id }}">Etiquetas</button><form method="post" action="{{ route('pipeline.send', $lead) }}">@csrf<button type="submit">Enviar para a pipeline</button></form></div></details></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <nav class="leads-pagination" aria-label="Paginação dos leads">{{ $leads->links('pagination.kaizen') }}</nav>
        @endif
    </section>
</div>

<dialog id="new-list" class="lead-modal"><form method="post" action="{{ route('leads.lists.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Organização</p><h2>Nova lista</h2><label>Nome da lista<input name="name" required maxlength="120" placeholder="Ex.: Contadores"></label><button type="submit" class="lead-button lead-button-primary">Criar lista</button></form></dialog>
<dialog id="new-label" class="lead-modal"><form method="post" action="{{ route('leads.labels.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Etiquetas</p><h2>Nova etiqueta</h2><label>Nome<input name="name" required maxlength="80"></label><label>Cor da etiqueta<input type="color" name="background_color" value="#dbeafe"></label><label>Cor do texto<input type="color" name="text_color" value="#1e3a8a"></label><button type="submit" class="lead-button lead-button-primary">Criar etiqueta</button></form></dialog>

<dialog id="new-lead" class="lead-modal"><form method="post" action="{{ route('leads.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Cadastro manual</p><h2>Novo lead</h2>@include('leads.partials.fields', ['lead' => null])<button type="submit" class="lead-button lead-button-primary">Cadastrar lead</button></form></dialog>


@foreach($leads as $lead)
<dialog id="label-{{ $lead->id }}" class="lead-modal"><form method="post" action="{{ route('leads.apply-label') }}" class="lead-form">@csrf<input type="hidden" name="lead_id" value="{{ $lead->id }}"><button type="button" class="lead-modal-close" data-dialog-close>×</button><h2>Etiquetas</h2><label>Aplicar etiqueta<select name="lead_label_id" required>@foreach($labels as $label)<option value="{{ $label->id }}">{{ $label->name }}</option>@endforeach</select></label><button type="submit" class="lead-button lead-button-primary">Aplicar</button></form></dialog>
<dialog id="lead-{{ $lead->id }}" class="lead-modal lead-detail-modal"><form method="post" action="{{ route('leads.update', $lead) }}" class="lead-form">@csrf @method('PUT')<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Lead #{{ $lead->id }}</p><h2>{{ $lead->name }}</h2><p class="lead-captured">Capturado em {{ optional($lead->created_at)->format('d/m/Y H:i') }}</p>@include('leads.partials.fields', ['lead' => $lead])<div class="lead-modal-actions"><button type="button" class="lead-button lead-button-secondary" data-dialog-close>Cancelar</button><button type="submit" class="lead-button lead-button-primary">Salvar alterações</button></div></form></dialog>
@endforeach
@endsection

@push('scripts')<script src="{{ asset('leads.js') }}?v={{ filemtime(public_path('leads.js')) }}" defer></script>@endpush



