@if ($paginator->hasPages())
    <nav class="platform-pagination" aria-label="ページ移動">
        @if ($paginator->onFirstPage())
            <span class="disabled">前へ</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">前へ</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="ellipsis">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">次へ</a>
        @else
            <span class="disabled">次へ</span>
        @endif
    </nav>
@endif
