@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex w-full min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
        <p class="shrink-0 text-center text-xs font-medium text-gray-500 dark:text-gray-400 sm:text-left">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <div class="flex max-w-full flex-wrap items-center justify-center gap-1 sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Anterior</a>
            @endif

            <span class="inline-flex h-7 items-center justify-center rounded-md px-2 text-xs font-bold text-white sm:hidden" style="background: var(--te-button-color, #022a8c);">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden h-7 items-center justify-center px-1 text-xs font-semibold text-gray-400 sm:inline-flex">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="hidden h-7 min-w-7 items-center justify-center rounded-md px-2 text-xs font-bold text-white sm:inline-flex" style="background: var(--te-button-color, #022a8c);">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="hidden h-7 min-w-7 items-center justify-center rounded-md border border-gray-200 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 sm:inline-flex">{{ $page }}</a>
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
