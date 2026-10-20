@extends('layouts.scraper-shell')

@section('title', 'Pipeline · Parceiros Kaizen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('pipeline.css') }}">
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
                            <span class="pipeline-column-count">{{ $stage->opportunities->count() }} {{ $stage->opportunities->count() === 1 ? 'oportunidade' : 'oportunidades' }}</span>
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
                            <article class="opportunity-card" draggable="true" data-opportunity-id="{{ $opportunity->id }}" data-move-url="{{ route('pipeline.move', $opportunity) }}">
                                <div class="opportunity-card-head"><span class="opportunity-drag" aria-hidden="true">⠿</span><span class="opportunity-source">{{ $opportunity->lead->source ?: 'Google Maps' }}</span></div>
                                <h3>{{ $opportunity->lead->name ?: 'Empresa sem nome' }}</h3>
                                <div class="opportunity-meta">
                                    @if($opportunity->lead->phone)<span>☎ {{ $opportunity->lead->phone }}</span>@endif
                                    @if($opportunity->lead->category)<span>{{ $opportunity->lead->category }}</span>@endif
                                    @if($opportunity->lead->address)<span>{{ $opportunity->lead->address }}</span>@endif
                                </div>
                                <label class="pipeline-move-select">Mover para
                                    <select class="pipeline-select" data-move-url="{{ route('pipeline.move', $opportunity) }}" aria-label="Mover {{ $opportunity->lead->name }}">
                                        @foreach($stages as $target)
                                            <option value="{{ $target->id }}" @selected($target->id === $stage->id)>{{ $target->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </article>
                        @empty
                            <div class="pipeline-column-empty"><span aria-hidden="true">＋</span><p>Nenhuma oportunidade nesta etapa</p><small>Arraste um card para cá</small></div>
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
        <p class="pipeline-board-hint">Arraste os cards para mover oportunidades. Use o seletor no card para uma alternativa acessível.</p>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('pipeline.js') }}" defer></script>
@endpush
