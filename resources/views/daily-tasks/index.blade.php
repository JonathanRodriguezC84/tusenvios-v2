@php
    $toneClasses = [
        'red' => ['panel' => 'border-red-200 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'text' => 'text-red-700', 'button' => 'bg-red-700 hover:bg-red-800 text-white', 'dot' => 'bg-red-500'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-700', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white', 'dot' => 'bg-blue-500'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'text' => 'text-amber-700', 'button' => 'bg-amber-600 hover:bg-amber-700 text-white', 'dot' => 'bg-amber-500'],
        'indigo' => ['panel' => 'border-indigo-200 bg-indigo-50', 'badge' => 'bg-indigo-100 text-indigo-800', 'text' => 'text-indigo-700', 'button' => 'bg-indigo-700 hover:bg-indigo-800 text-white', 'dot' => 'bg-indigo-500'],
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-700', 'button' => 'bg-emerald-700 hover:bg-emerald-800 text-white', 'dot' => 'bg-emerald-500'],
        'slate' => ['panel' => 'border-gray-200 bg-gray-50', 'badge' => 'bg-gray-200 text-gray-800', 'text' => 'text-gray-700', 'button' => 'bg-gray-800 hover:bg-gray-900 text-white', 'dot' => 'bg-gray-500'],
    ];

    $modeClasses = [
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'title' => 'text-emerald-950', 'text' => 'text-emerald-800', 'button' => 'bg-emerald-700 hover:bg-emerald-800 text-white'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'title' => 'text-blue-950', 'text' => 'text-blue-800', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white'],
        'red' => ['panel' => 'border-red-200 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'title' => 'text-red-950', 'text' => 'text-red-800', 'button' => 'bg-red-700 hover:bg-red-800 text-white'],
    ];
    $modeTone = $modeClasses[$modeContent['tone']] ?? $modeClasses['blue'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tareas Diarias" description="Lo que tu negocio debe revisar hoy para mantener las guias al dia.">
            <x-slot name="eyebrow">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</x-slot>
            <x-slot name="actions">
                <a href="{{ route('shipments.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Ver guias</a>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        <section id="estado-dia" class="scroll-mt-24 mb-5 rounded-lg border p-5 shadow-sm {{ $modeTone['panel'] }}">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black uppercase tracking-wider {{ $modeTone['badge'] }}">{{ $modeContent['label'] }}</span>
                    <h2 class="mt-3 text-xl font-black {{ $modeTone['title'] }}">{{ $modeContent['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold {{ $modeTone['text'] }}">{{ $modeContent['description'] }}</p>
                    <p class="mt-3 text-sm text-gray-700">{{ $assistantMessage }}</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <span class="rounded-lg border border-white/70 bg-white/80 px-3 py-2 text-xs font-black text-gray-700">{{ $summary['total'] }} pendiente(s)</span>
                        <span class="rounded-lg border border-white/70 bg-white/80 px-3 py-2 text-xs font-black {{ $summary['urgent'] > 0 ? 'text-red-700' : 'text-gray-700' }}">{{ $summary['urgent'] }} urgente(s)</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center xl:justify-end">
                    <a href="{{ $startUrl }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-bold shadow-sm {{ $modeTone['button'] }}">Empezar mi dia</a>
                    <button type="button" id="copy-daily-summary" data-summary="{{ $summaryText }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">Copiar reporte del dia</button>
                    <span id="daily-summary-copy-status" class="min-h-5 text-xs font-bold text-emerald-700" aria-live="polite"></span>
                </div>
            </div>
        </section>

        @if ($visibleCards->isNotEmpty())
            <section id="tareas" class="scroll-mt-24 mt-5">
                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Tareas activas</p>
                        <h3 class="text-lg font-black text-gray-950">Trabaja solo lo que necesita accion</h3>
                    </div>
                    <a href="{{ route('shipments.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver todas las guias</a>
                </div>
                <div class="grid gap-4 xl:grid-cols-2">
            @foreach ($visibleCards as $card)
                @php $tone = $toneClasses[$card['tone']] ?? $toneClasses['slate']; @endphp
                <article class="rounded-lg border bg-white shadow-sm {{ $card['count'] > 0 ? $tone['panel'] : 'border-gray-200' }}">
                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $tone['badge'] }}">{{ $card['priority'] }}</span>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $card['count'] }} guia(s)</span>
                            </div>
                            <h3 class="mt-2 text-lg font-black text-gray-950">{{ $card['title'] }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $card['description'] }}</p>
                        </div>
                        <a href="{{ $card['route'] }}" class="inline-flex shrink-0 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold shadow-sm {{ $card['count'] > 0 ? $tone['button'] : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                            {{ $card['action'] }}
                        </a>
                    </div>

                    <div class="border-t border-white/70 bg-white">
                        @forelse ($card['shipments'] as $shipment)
                            @php
                                $primaryAction = match ($shipment->status) {
                                    'created' => ['label' => 'Imprimir ahora', 'url' => route('shipments.print', $shipment), 'print' => true],
                                    'failed_delivery', 'rescheduled', 'return_pending' => ['label' => 'Resolver novedad', 'url' => route('shipments.show', $shipment), 'print' => false],
                                    'on_route' => ['label' => 'Actualizar entrega', 'url' => route('shipments.show', $shipment), 'print' => false],
                                    'printed', 'in_warehouse', 'in_sorting', 'assigned' => ['label' => 'Actualizar estado', 'url' => route('shipments.show', $shipment), 'print' => false],
                                    default => ['label' => 'Abrir guia', 'url' => route('shipments.show', $shipment), 'print' => false],
                                };
                            @endphp
                            <div class="grid gap-3 border-b border-gray-100 px-5 py-3 last:border-b-0 sm:grid-cols-[1fr_auto] sm:items-center">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $tone['dot'] }}"></span>
                                        <p class="font-mono text-sm font-black text-gray-950">{{ $shipment->guide_number }}</p>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-700">{{ $statusLabels[$shipment->status] ?? $shipment->status }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm font-semibold text-gray-800">{{ $shipment->recipient_name }} {{ $shipment->recipient_lastname }}</p>
                                    <p class="text-xs text-gray-500">
                                        Actualizada {{ $shipment->updated_at->diffForHumans() }}
                                        @if ($shipment->recipient_locality || $shipment->recipient_city)
                                            - {{ $shipment->recipient_city ?: $shipment->recipient_locality }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    @if ($primaryAction['print'])
                                        <a href="{{ $primaryAction['url'] }}" onclick="event.preventDefault(); window.open(this.href, 'print{{ $shipment->id }}', 'width=800,height=600,scrollbars=yes,resizable=yes')" class="rounded-md bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800">{{ $primaryAction['label'] }}</a>
                                        <a href="{{ route('shipments.show', $shipment) }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Abrir</a>
                                    @else
                                        <a href="{{ $primaryAction['url'] }}" class="rounded-md bg-blue-700 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800">{{ $primaryAction['label'] }}</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-6">
                                <p class="text-sm font-semibold text-gray-500">Sin guias en esta tarea.</p>
                            </div>
                        @endforelse
                    </div>
                </article>
            @endforeach
                </div>
            </section>
        @endif
    </div>

    <script>
        const copyDailySummary = document.getElementById('copy-daily-summary');

        if (copyDailySummary) {
            copyDailySummary.addEventListener('click', async () => {
                const status = document.getElementById('daily-summary-copy-status');
                const text = copyDailySummary.dataset.summary || '';

                try {
                    await navigator.clipboard.writeText(text);
                } catch (error) {
                    const fallback = document.createElement('textarea');
                    fallback.value = text;
                    fallback.setAttribute('readonly', '');
                    fallback.style.position = 'fixed';
                    fallback.style.opacity = '0';
                    document.body.appendChild(fallback);
                    fallback.select();
                    document.execCommand('copy');
                    fallback.remove();
                }

                if (status) {
                    status.textContent = 'Reporte copiado';
                    window.setTimeout(() => {
                        status.textContent = '';
                    }, 2500);
                }
            });
        }
    </script>
</x-app-layout>
