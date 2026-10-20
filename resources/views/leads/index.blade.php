@extends('layouts.scraper-shell')

@section('title', 'Leads · Parceiros Kaizen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('leads.css') }}">
@endpush

@section('content')
    <header class="module-header">
        <div>
            <p class="module-eyebrow">Módulo</p>
            <h1>Leads</h1>
            <p class="module-description">Resultados pertencentes à sua conta.</p>
        </div>
        <span class="module-count">{{ $leads->total() }} {{ $leads->total() === 1 ? 'lead' : 'leads' }}</span>
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
@endsection
