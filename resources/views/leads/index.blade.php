@extends('layouts.scraper-shell')

@section('title', 'Leads · Parceiros Kaizen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('leads.css') }}?v={{ filemtime(public_path('leads.css')) }}">
@endpush

@section('content')
    <section class="lead-lists"><h2>Listas de Leads</h2><form method="post" action="{{ route('leads.lists.store') }}">@csrf<input name="name" required maxlength="120" placeholder="Nome da lista"><button type="submit">Criar lista</button></form>@if($lists->isNotEmpty())<div class="lead-list-chips">@foreach($lists as $list)<span>{{ $list->name }} ({{ $list->leads()->count() }})</span>@endforeach</div>@endif</section>
    <header class="module-header">
        <div>
            <p class="module-eyebrow">Módulo</p>
            <h1>Leads</h1>
            <p class="module-description">Resultados pertencentes à sua conta.</p>
        </div>
        <div class="module-actions"><span class="module-count">{{ $leads->total() }} {{ $leads->total() === 1 ? 'lead' : 'leads' }}</span><button type="button" class="lead-new-button" onclick="document.getElementById('new-lead').showModal()">+ Novo lead</button></div>
    </header>

    <form class="lead-search" method="get" action="{{ route('leads') }}" role="search">
        <label class="sr-only" for="lead-search">Buscar leads</label>
        <input id="lead-search" name="q" value="{{ $term }}" placeholder="Buscar por nome, telefone ou endereço">
        <button type="submit">Buscar</button>
    </form>

    @if($leads->isEmpty())
        <section class="lead-empty" aria-live="polite">
            <strong>Nenhum lead encontrado</strong>
            <p>{{ $term ? 'Tente outro termo de busca.' : 'Os leads das suas buscas concluídas aparecerão aqui.' }}</p>
        </section>
    @else
        <div class="lead-list">
            @foreach($leads as $lead)
<article class="lead-card">
 @if($lead->list)<p class="lead-card__list">Lista: {{ $lead->list->name }}</p>@endif
                    <div class="lead-card-main">
                        <p class="lead-card-source">{{ $lead->job?->location ?: 'Busca de prospecção' }}</p>
                        <h2>{{ $lead->name ?: 'Empresa sem nome' }}</h2>
                        <p>{{ $lead->address ?: 'Endereço não informado' }}</p>
                        <form method="post" action="{{ route('pipeline.send', $lead) }}">
                            @csrf
                            <button type="submit">Enviar para a pipeline</button>
                        </form>
                    </div>
                    <dl>
                        <div><dt>Telefone</dt><dd>{{ $lead->phone ?: 'Não informado' }}</dd></div>
                        <div><dt>Site</dt><dd>{{ $lead->website ?: 'Não informado' }}</dd></div>
                        <div><dt>Capturado em</dt><dd>{{ optional($lead->created_at)->format('d/m/Y H:i') }}</dd></div>
                        <div><dt>Origem</dt><dd>{{ $lead->source ?: 'Google Maps' }}</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>
        <nav class="leads-pagination" aria-label="Paginação dos leads">{{ $leads->links() }}</nav>
    @endif
<dialog id="new-lead" class="lead-modal"><form method="post" action="{{ route('leads.store') }}" class="lead-form">@csrf<button type="button" class="lead-modal-close" onclick="this.closest('dialog').close()" aria-label="Fechar">×</button><h2>Novo lead</h2><label>Nome do contato ou empresa<input name="name" required value="{{ old('name') }}"></label><label>Empresa (opcional)<input name="company" value="{{ old('company') }}"></label><label>Telefone<input name="phone" value="{{ old('phone') }}"></label><label>E-mail<input type="email" name="email" value="{{ old('email') }}"></label><label>Site<input type="url" name="website" value="{{ old('website') }}"></label><label>Observações<textarea name="observations">{{ old('observations') }}</textarea></label><button type="submit">Cadastrar lead</button></form></dialog>
@endsection
