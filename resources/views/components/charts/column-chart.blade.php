@props([
    'data' => [],
    'color' => 'var(--viz-cat-1)',
    'format' => 'number',
])
@php
    $count = count($data);
    $values = array_column($data, 'value');
    $rawMax = $values !== [] ? max($values) : 0;

    $niceMax = (function (float $n) {
        if ($n <= 0) return 1;
        $magnitude = 10 ** floor(log10($n));
        foreach ([1, 2, 2.5, 5, 10] as $step) {
            $candidate = $step * $magnitude;
            if ($candidate >= $n) return $candidate;
        }
        return ceil($n / $magnitude) * $magnitude;
    })((float) $rawMax);

    $thinStep = $count > 10 ? (int) ceil($count / 7) : 1;

    $formatValue = function ($value) use ($format) {
        return $format === 'currency'
            ? '$' . number_format((float) $value, 0, ',', '.')
            : number_format((float) $value, 0, ',', '.');
    };

    $plotHeight = 116;
@endphp

<div class="flex items-stretch gap-2">
    <div class="flex w-9 shrink-0 flex-col justify-between text-right text-[10px] font-bold text-[var(--viz-muted)]" style="height: {{ $plotHeight }}px">
        <span>{{ $formatValue($niceMax) }}</span>
        <span>{{ $formatValue($niceMax / 2) }}</span>
        <span>0</span>
    </div>

    <div class="min-w-0 flex-1 overflow-x-auto pb-1">
        <div class="relative flex min-w-max items-end gap-2" style="height: {{ $plotHeight }}px">
            <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-[var(--viz-grid)]"></div>
            <div class="pointer-events-none absolute inset-x-0 border-t border-[var(--viz-grid)]" style="top: 50%"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 border-t border-[var(--viz-axis)]"></div>

            @forelse ($data as $i => $d)
                @php
                    $isLast = $i === $count - 1;
                    $barHeight = $d['value'] > 0 ? max(3, (int) round(($d['value'] / $niceMax) * $plotHeight)) : 0;
                    $showLabel = $count <= 10 || $i % $thinStep === 0 || $isLast;
                @endphp
                <div class="group relative flex h-full w-7 shrink-0 flex-col items-center justify-end" tabindex="0">
                    <div class="pointer-events-none absolute -top-7 z-10 hidden whitespace-nowrap rounded-md px-2 py-1 text-[11px] font-bold shadow-lg group-hover:block group-focus:block" style="background: var(--viz-tooltip-bg); color: var(--viz-tooltip-text)">
                        {{ $d['sub'] ?? $d['label'] }}: {{ $formatValue($d['value']) }}
                    </div>

                    @if ($isLast)
                        <span class="mb-1 text-[11px] font-black" style="color: var(--viz-primary)">{{ $formatValue($d['value']) }}</span>
                    @endif

                    <div class="w-6 rounded-t transition-opacity group-hover:opacity-75"
                         style="height: {{ $barHeight }}px; min-height: {{ $d['value'] > 0 ? '2px' : '0' }}; background: {{ $color }};"></div>

                    <span class="mt-1 text-center text-[10px] font-bold leading-tight text-[var(--viz-muted)]">
                        {{ $showLabel ? $d['label'] : "\u{00A0}" }}
                    </span>
                </div>
            @empty
                <p class="text-sm font-semibold" style="color: var(--viz-muted)">Todavia no hay datos.</p>
            @endforelse
        </div>
    </div>
</div>
