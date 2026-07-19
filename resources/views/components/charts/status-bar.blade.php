@props([
    'buckets' => [],
    'total' => 0,
])
@php
    $safeTotal = max(1, $total);
    $catColors = [
        1 => '#2a78d6', 2 => '#1baf7a', 3 => '#eda100', 4 => '#008300',
        5 => '#4a3aa7', 6 => '#e34948', 7 => '#e87ba4', 8 => '#eb6834',
    ];
@endphp

<div>
    <div class="flex h-6 w-full overflow-hidden rounded-md" style="background: #e1e0d9">
        @foreach ($buckets as $b)
            @continue($b['count'] <= 0)
            @php $pct = max(1.5, round(($b['count'] / $safeTotal) * 100, 2)); @endphp
            <div class="group relative h-full border-r-2 last:border-r-0"
                 style="width: {{ $pct }}%; background: {{ $catColors[$b['slot']] ?? '#2a78d6' }}; border-color: #ffffff;"
                 tabindex="0">
                <div class="pointer-events-none absolute left-1/2 top-full z-10 mt-1.5 hidden -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-[11px] font-bold shadow-lg group-hover:block group-focus:block"
                     style="background: #0b0b0b; color: #ffffff">
                    {{ $b['label'] }}: {{ number_format($b['count'], 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5">
        @foreach ($buckets as $b)
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 shrink-0 rounded-sm" style="background: {{ $catColors[$b['slot']] ?? '#2a78d6' }}"></span>
                <span class="text-xs font-bold" style="color: #52514e">{{ $b['label'] }}</span>
                <span class="text-xs font-black" style="color: #0b0b0b">{{ number_format($b['count'], 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>
</div>
