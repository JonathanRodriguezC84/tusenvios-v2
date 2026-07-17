@props([
    'score' => 0,
    'size' => 144,
    'stroke' => 12,
    'color' => '#2563eb',
    'track' => '#e5e7eb',
])

@php
    $radius = ($size - $stroke) / 2;
    $circumference = 2 * M_PI * $radius;
    $clamped = max(0, min(100, (float) $score));
    $offset = $circumference * (1 - $clamped / 100);
    $center = $size / 2;
@endphp

<div {{ $attributes->merge(['class' => 'relative grid place-items-center']) }} style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg viewBox="0 0 {{ $size }} {{ $size }}" width="{{ $size }}" height="{{ $size }}" class="-rotate-90">
        <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="{{ $track }}" stroke-width="{{ $stroke }}" />
        <circle
            cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}"
            fill="none" stroke="{{ $color }}" stroke-width="{{ $stroke }}"
            stroke-linecap="round"
            stroke-dasharray="{{ $circumference }}"
            class="ring-gauge-arc"
            style="--ring-gauge-offset: {{ $offset }}; --ring-gauge-circumference: {{ $circumference }};"
        />
    </svg>
    <div class="absolute inset-0 grid place-items-center">
        {{ $slot }}
    </div>
</div>
