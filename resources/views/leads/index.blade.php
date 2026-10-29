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
                <label class="sr-only" for="lead-search">Buscar leads</label><input id="lead-search" name="q" value="{{ $term }}" placeholder="Buscar por nome, e-mail, telefone ou endereço"><button type="submit" class="lead-button lead-button-primary">Buscar</button>
            </form>
            <form id="bulk-pipeline-form" method="post" action="{{ route('pipeline.send-many') }}">@csrf<div id="bulk-lead-ids"></div><button type="submit" class="lead-button lead-button-secondary" id="bulk-pipeline" disabled>Enviar selecionados para a pipeline</button></form>
        </div>

        @if($leads->isEmpty())
            <div class="lead-empty"><strong>Nenhum lead encontrado</strong><p>{{ $term ? 'Tente outro termo de busca.' : 'Os leads das suas buscas concluídas aparecerão aqui.' }}</p></div>
        @else
            <div class="lead-table-wrap">
                <table class="lead-table">
                    <thead><tr><th><input type="checkbox" id="select-all" aria-label="Selecionar todos"></th><th>Nome</th><th>Contato</th><th>Endereço</th><th>Lista</th><th>Capturado em</th><th><span class="sr-only">Ações</span></th></tr></thead>
                    <tbody>
                    @foreach($leads as $lead)
                        <tr class="lead-row" data-dialog-open="lead-{{ $lead->id }}" tabindex="0">
                            <td><input class="lead-select" type="checkbox" value="{{ $lead->id }}" aria-label="Selecionar {{ $lead->name }}"></td>
                            <td><strong>{{ $lead->name ?: 'Empresa sem nome' }}</strong>@if($lead->category)<small>{{ $lead->category }}</small>@endif</td>
                            <td><span>{{ $lead->email ?: 'E-mail não informado' }}</span><small>{{ $lead->phone ?: 'Telefone não informado' }}</small>@if($lead->website)<a href="{{ $lead->website }}" target="_blank" rel="noopener">Abrir site</a>@endif</td>
                            <td>{{ $lead->address ?: 'Não informado' }}</td>
                            <td>{{ $lead->list?->name ?: 'Sem lista' }}</td>
                            <td>{{ optional($lead->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="lead-actions-cell"><details class="lead-menu"><summary aria-label="Ações do lead">•••</summary><div><button type="button" data-dialog-open="lead-{{ $lead->id }}">Ver e editar</button><form method="post" action="{{ route('pipeline.send', $lead) }}">@csrf<button type="submit">Enviar para a pipeline</button></form></div></details></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <nav class="leads-pagination" aria-label="Paginação dos leads">{{ $leads->links() }}</nav>
        @endif
    </section>
</div>

<dialog id="new-list" class="lead-modal"><form method="post" action="{{ route('leads.lists.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Organização</p><h2>Nova lista</h2><label>Nome da lista<input name="name" required maxlength="120" placeholder="Ex.: Contadores"></label><button type="submit" class="lead-button lead-button-primary">Criar lista</button></form></dialog>

<dialog id="new-lead" class="lead-modal"><form method="post" action="{{ route('leads.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Cadastro manual</p><h2>Novo lead</h2>@include('leads.partials.fields', ['lead' => null])<button type="submit" class="lead-button lead-button-primary">Cadastrar lead</button></form></dialog>

@foreach($leads as $lead)
<dialog id="lead-{{ $lead->id }}" class="lead-modal lead-detail-modal"><form method="post" action="{{ route('leads.update', $lead) }}" class="lead-form">@csrf @method('PUT')<button type="button" class="lead-modal-close" data-dialog-close aria-label="Fechar">×</button><p class="module-eyebrow">Lead #{{ $lead->id }}</p><h2>{{ $lead->name }}</h2><p class="lead-captured">Capturado em {{ optional($lead->created_at)->format('d/m/Y H:i') }}</p>@include('leads.partials.fields', ['lead' => $lead])<div class="lead-modal-actions"><button type="button" class="lead-button lead-button-secondary" data-dialog-close>Cancelar</button><button type="submit" class="lead-button lead-button-primary">Salvar alterações</button></div></form></dialog>
@endforeach
@endsection

@push('scripts')<script src="{{ asset('leads.js') }}?v={{ filemtime(public_path('leads.js')) }}" defer></script>@endpush
