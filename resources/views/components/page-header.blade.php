<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        @if ($eyebrow ?? false)
            <p class="text-xs font-black uppercase tracking-wider text-blue-700">{{ $eyebrow }}</p>
        @endif
        <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h2>
        @if ($description ?? false)
            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @if ($actions ?? false)
        <div class="flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
