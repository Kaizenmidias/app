@extends('layouts.scraper-shell')

@section('title', 'Pipeline · Parceiros Kaizen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('pipeline.css') }}?v={{ filemtime(public_path('pipeline.css')) }}">
@endpush

@section('content')
    @php($opportunityCount = $stages->sum(fn ($stage) => $stage->opportunities->count()))
    @php($stageIds = $stages->pluck('id')->values()->all())

    <header class="kanban-header">
        <div>
            <p class="module-eyebrow">Módulo comercial</p>
            <h1>Pipeline</h1>
            <p class="module-description">Acompanhe suas oportunidades por etapa e mantenha cada próximo passo visível.</p>
        </div>
        <div class="kanban-header-actions">
            <span class="module-count">{{ $opportunityCount }} {{ $opportunityCount === 1 ? 'oportunidade' : 'oportunidades' }}</span>
            <button type="button" class="pipeline-button pipeline-button-primary" data-dialog-open="new-stage-dialog">Nova etapa</button>
        </div>
    </header>

    @if(session('status'))
        <p class="pipeline-status" role="status">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <p class="pipeline-error" role="alert">{{ $errors->first() }}</p>
    @endif

    <dialog id="new-stage-dialog" class="pipeline-dialog">
        <form method="post" action="{{ route('pipeline.stages.store') }}">
            @csrf
            <div class="pipeline-dialog-head"><div><p class="module-eyebrow">Pipeline</p><h2>Nova etapa</h2></div><button type="button" class="dialog-close" data-dialog-close aria-label="Fechar">×</button></div>
            <label for="new-stage-name">Nome da etapa</label>
            <input id="new-stage-name" name="name" maxlength="80" required placeholder="Ex.: Primeiro contato">
            <div class="pipeline-dialog-actions"><button type="button" class="pipeline-button pipeline-button-ghost" data-dialog-close>Cancelar</button><button type="submit" class="pipeline-button pipeline-button-primary">Criar etapa</button></div>
        </form>
    </dialog>

    @if($stages->isEmpty())
        <section class="pipeline-empty">
            <div class="pipeline-empty-icon" aria-hidden="true">+</div>
            <h2>Crie sua primeira etapa</h2>
            <p>Comece com uma etapa como “Novo lead”. Ela será o destino padrão das novas oportunidades.</p>
            <button type="button" class="pipeline-button pipeline-button-primary" data-dialog-open="new-stage-dialog">Criar primeira etapa</button>
        </section>
    @else
        <div class="pipeline-board" aria-label="Quadro de oportunidades">
            @foreach($stages as $index => $stage)
                @php($stageBudget = $stage->opportunities->sum(fn ($opportunity) => (float) ($opportunity->lead->budget_value ?? 0)))
                @php($leftIds = $stageIds)
                @php($rightIds = $stageIds)
                @if($index > 0)
                    @php([$leftIds[$index - 1], $leftIds[$index]] = [$leftIds[$index], $leftIds[$index - 1]])
                @endif
                @if($index < count($stageIds) - 1)
                    @php([$rightIds[$index], $rightIds[$index + 1]] = [$rightIds[$index + 1], $rightIds[$index]])
                @endif
                <section class="pipeline-column" data-stage-id="{{ $stage->id }}" aria-labelledby="stage-{{ $stage->id }}-title">
                    <header class="pipeline-column-head">
                        <div class="pipeline-column-title">
                            <span class="pipeline-column-kicker">Etapa {{ $index + 1 }}</span>
                            <h2 id="stage-{{ $stage->id }}-title">{{ $stage->name }}</h2>
                            <span class="pipeline-column-count" data-column-count>{{ $stage->opportunities->count() }} {{ $stage->opportunities->count() === 1 ? 'oportunidade' : 'oportunidades' }}</span>
                            <strong class="pipeline-column-budget" data-column-budget>R$ {{ number_format($stageBudget, 2, ',', '.') }}</strong>
                        </div>
                        <details class="stage-menu">
                            <summary aria-label="Ações da etapa {{ $stage->name }}">⋯</summary>
                            <div class="stage-menu-panel">
                                <button type="button" data-dialog-open="rename-stage-{{ $stage->id }}">Renomear</button>
                                @if($index > 0)
                                    <form method="post" action="{{ route('pipeline.stages.reorder') }}">
                                        @csrf
                                        @foreach($leftIds as $leftId)<input type="hidden" name="stage_ids[]" value="{{ $leftId }}">@endforeach
                                        <button type="submit">Mover para a esquerda</button>
                                    </form>
                                @endif
                                @if($index < count($stageIds) - 1)
                                    <form method="post" action="{{ route('pipeline.stages.reorder') }}">
                                        @csrf
                                        @foreach($rightIds as $rightId)<input type="hidden" name="stage_ids[]" value="{{ $rightId }}">@endforeach
                                        <button type="submit">Mover para a direita</button>
                                    </form>
                                @endif
                                <button type="button" class="menu-danger" data-dialog-open="delete-stage-{{ $stage->id }}">Excluir etapa</button>
                            </div>
                        </details>
                    </header>

                    <div class="pipeline-cards" data-drop-target="{{ $stage->id }}">
                        @forelse($stage->opportunities as $opportunity)
                            @php($lead = $opportunity->lead)
                            @php($leadCity = data_get($lead->raw_data, 'city') ?: data_get($lead->raw_data, 'cityName'))
                            @php($leadState = data_get($lead->raw_data, 'stateCode') ?: data_get($lead->raw_data, 'state'))
                            @php($leadLocation = implode(' / ', array_filter([$leadCity, $leadState])))
                            <article class="opportunity-card" draggable="true" role="button" tabindex="0" data-dialog-open="lead-dialog-{{ $lead->id }}" data-opportunity-id="{{ $opportunity->id }}" data-budget="{{ (float) ($lead->budget_value ?? 0) }}" data-move-url="{{ route('pipeline.move', $opportunity) }}" aria-label="Abrir informações de {{ $lead->name ?: 'Empresa sem nome' }}">
                                <h3>{{ $opportunity->lead->name ?: 'Empresa sem nome' }}</h3>
                                <strong class="opportunity-budget">R$ {{ number_format((float) ($lead->budget_value ?? 0), 2, ',', '.') }}</strong>
                                <span class="opportunity-location">{{ $leadLocation ?: 'Localização não informada' }}</span>
                            </article>
                            <dialog id="lead-dialog-{{ $lead->id }}" class="pipeline-dialog pipeline-lead-dialog">
                                <div class="pipeline-lead-detail">
                                    <div class="pipeline-dialog-head"><div><p class="module-eyebrow">Informações do lead</p><h2>{{ $lead->name ?: 'Empresa sem nome' }}</h2></div><button type="button" class="dialog-close" data-dialog-close aria-label="Fechar">×</button></div>
                                    <form method="post" action="{{ route('leads.update', $lead) }}" class="pipeline-stage-checklist">@csrf @method('PUT')<input type="hidden" name="name" value="{{ $lead->name }}"><input type="hidden" name="email" value="{{ $lead->email }}"><div><strong>Etapa do funil</strong><small>Atualize o avanço comercial deste lead.</small></div><div class="pipeline-stage-options"><label><input type="radio" name="funnel_stage" value="contacted" @checked(data_get($lead->raw_data,'funnel_stage')==='contacted')><span>Contato feito</span></label><label><input type="radio" name="funnel_stage" value="meeting" @checked(data_get($lead->raw_data,'funnel_stage')==='meeting')><span>Reunião agendada</span></label><label><input type="radio" name="funnel_stage" value="closed" @checked(data_get($lead->raw_data,'funnel_stage')==='closed')><span>Contrato fechado</span></label></div><button type="submit" class="pipeline-button pipeline-button-primary">Salvar etapa</button></form>
                                    <div class="pipeline-lead-grid">
                                        <div><span>Orçamento</span><strong>R$ {{ number_format((float) ($lead->budget_value ?? 0), 2, ',', '.') }}</strong></div>
                                        <div><span>Etapa</span><strong>{{ $stage->name }}</strong></div>
                                        <div><span>E-mail</span><strong>{{ $lead->email ?: 'Não informado' }}</strong></div>
                                        <div><span>Telefone</span><strong>{{ $lead->phone ?: 'Não informado' }}</strong></div>
                                        <div><span>Site</span>@if($lead->website)<a href="{{ $lead->website }}" target="_blank" rel="noopener">Abrir site</a>@else<strong>Não informado</strong>@endif</div>
                                        <div><span>Instagram</span>@if($lead->instagram)<a href="{{ $lead->instagram }}" target="_blank" rel="noopener">Abrir Instagram</a>@else<strong>Não informado</strong>@endif</div>
                                        <div><span>LinkedIn</span>@if($lead->linkedin)<a href="{{ $lead->linkedin }}" target="_blank" rel="noopener">Abrir LinkedIn</a>@else<strong>Não informado</strong>@endif</div>
                                        <div><span>Setor</span><strong>{{ $lead->sector ?: 'Não informado' }}</strong></div>
                                        <div><span>Receita estimada</span><strong>{{ $lead->estimated_revenue !== null ? 'R$ '.number_format((float) $lead->estimated_revenue, 2, ',', '.') : 'Não informado' }}</strong></div>
                                        <div><span>Número de funcionários</span><strong>{{ $lead->employee_count ?? 'Não informado' }}</strong></div>
                                        <div><span>Responsável</span><strong>{{ $lead->responsible?->name ?: 'Sem responsável' }}</strong></div>
                                        <div><span>Etiquetas</span><strong>{{ implode(', ', $lead->tags ?? []) ?: 'Nenhuma etiqueta' }}</strong></div>
                                        <div class="pipeline-lead-wide"><span>Endereço</span><strong>{{ $lead->address ?: 'Não informado' }}</strong></div>
                                        <div class="pipeline-lead-wide pipeline-lead-notes"><span>Anotações</span>@if(filled(data_get($lead->raw_data, 'manual_observations')))<strong>{{ data_get($lead->raw_data, 'manual_observations') }}</strong>@endif @forelse($lead->notes as $note)<p>{{ $note->content }}</p>@empty @if(blank(data_get($lead->raw_data, 'manual_observations')))<strong>Nenhuma anotação</strong>@endif @endforelse</div>
                                    </div>
                                    <section class="pipeline-lead-history"><h3>Histórico de atividade</h3>@forelse($lead->activities as $activity)<article><strong>{{ $activity->description }}</strong><time>{{ $activity->created_at->format('d/m/Y H:i') }}</time></article>@empty<p>Nenhuma atividade registrada.</p>@endforelse</section>
                                    <div class="pipeline-dialog-actions"><button type="button" class="pipeline-button pipeline-button-primary" data-dialog-close>Fechar</button></div>
                                </div>
                            </dialog>
                        @empty
                            <div class="pipeline-column-empty"><span aria-hidden="true">ï¼‹</span><p>Nenhuma oportunidade nesta etapa</p><small>Arraste um card para cá</small></div>
                        @endforelse
                    </div>
                </section>

                <dialog id="rename-stage-{{ $stage->id }}" class="pipeline-dialog">
                    <form method="post" action="{{ route('pipeline.stages.update', $stage) }}">
                        @csrf @method('PATCH')
                        <div class="pipeline-dialog-head"><div><p class="module-eyebrow">Editar etapa</p><h2>Renomear</h2></div><button type="button" class="dialog-close" data-dialog-close aria-label="Fechar">×</button></div>
                        <label for="stage-name-{{ $stage->id }}">Nome da etapa</label>
                        <input id="stage-name-{{ $stage->id }}" name="name" value="{{ $stage->name }}" maxlength="80" required>
                        <div class="pipeline-dialog-actions"><button type="button" class="pipeline-button pipeline-button-ghost" data-dialog-close>Cancelar</button><button type="submit" class="pipeline-button pipeline-button-primary">Salvar nome</button></div>
                    </form>
                </dialog>

                <dialog id="delete-stage-{{ $stage->id }}" class="pipeline-dialog">
                    <form method="post" action="{{ route('pipeline.stages.destroy', $stage) }}">
                        @csrf @method('DELETE')
                        <div class="pipeline-dialog-head"><div><p class="module-eyebrow">Atenção</p><h2>Excluir etapa</h2></div><button type="button" class="dialog-close" data-dialog-close aria-label="Fechar">×</button></div>
                        @if($stage->opportunities->isEmpty())
                            <p>Esta etapa está vazia e poderá ser removida com segurança.</p>
                        @else
                            @if($stages->count() === 1)
                                <p>Esta etapa tem <strong>{{ $stage->opportunities->count() }} {{ $stage->opportunities->count() === 1 ? 'oportunidade' : 'oportunidades' }}</strong> e é a única etapa. Crie outra etapa antes de excluir esta.</p>
                            @else
                                <p>Esta etapa tem <strong>{{ $stage->opportunities->count() }} {{ $stage->opportunities->count() === 1 ? 'oportunidade' : 'oportunidades' }}</strong>. Escolha para onde transferi-las antes de excluir.</p>
                                <label for="destination-{{ $stage->id }}">Etapa de destino</label>
                                <select id="destination-{{ $stage->id }}" name="destination_stage_id" required>
                                    <option value="">Selecione uma etapa</option>
                                    @foreach($stages->where('id', '!=', $stage->id) as $target)<option value="{{ $target->id }}">{{ $target->name }}</option>@endforeach
                                </select>
                            @endif
                        @endif
                        <div class="pipeline-dialog-actions"><button type="button" class="pipeline-button pipeline-button-ghost" data-dialog-close>Cancelar</button>@if($stage->opportunities->isEmpty() || $stages->count() > 1)<button type="submit" class="pipeline-button pipeline-button-danger">Excluir etapa</button>@else<button type="button" class="pipeline-button pipeline-button-danger" disabled>Crie outra etapa primeiro</button>@endif</div>
                    </form>
                </dialog>
            @endforeach
        </div>
        <p class="pipeline-board-hint">Clique em um card para ver os detalhes. Arraste os cards entre as colunas para mover as oportunidades.</p>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('pipeline.js') }}?v={{ filemtime(public_path('pipeline.js')) }}" defer></script>
@endpush

