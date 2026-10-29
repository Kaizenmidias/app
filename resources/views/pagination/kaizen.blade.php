@if ($paginator->hasPages())
    <div class="lead-pagination-nav">
        <p>Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados</p>
        <div class="lead-pagination-links">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true">« Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">« Anterior</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima »</a>
            @else
                <span aria-disabled="true">Próxima »</span>
            @endif
        </div>
    </div>
@endif
