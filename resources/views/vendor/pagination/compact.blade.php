@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-3">
        <p class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-7 items-center justify-center px-1 text-xs font-semibold text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-xs font-bold text-white" style="background: var(--te-button-color, #022a8c);">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Siguiente</a>
            @else
                <span class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
