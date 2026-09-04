@php
    $toneClasses = [
        'red' => ['panel' => 'border-red-200 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'countPill' => 'bg-red-700 text-white', 'text' => 'text-red-700', 'button' => 'bg-red-700 hover:bg-red-800 text-white', 'dot' => 'bg-red-500'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'countPill' => 'bg-blue-700 text-white', 'text' => 'text-blue-700', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white', 'dot' => 'bg-blue-500'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'countPill' => 'bg-amber-600 text-white', 'text' => 'text-amber-700', 'button' => 'bg-amber-600 hover:bg-amber-700 text-white', 'dot' => 'bg-amber-500'],
        'indigo' => ['panel' => 'border-indigo-200 bg-indigo-50', 'badge' => 'bg-indigo-100 text-indigo-800', 'countPill' => 'bg-indigo-700 text-white', 'text' => 'text-indigo-700', 'button' => 'bg-indigo-700 hover:bg-indigo-800 text-white', 'dot' => 'bg-indigo-500'],
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'countPill' => 'bg-emerald-700 text-white', 'text' => 'text-emerald-700', 'button' => 'bg-emerald-700 hover:bg-emerald-800 text-white', 'dot' => 'bg-emerald-500'],
        'slate' => ['panel' => 'border-gray-200 bg-gray-50', 'badge' => 'bg-gray-200 text-gray-800', 'countPill' => 'bg-gray-800 text-white', 'text' => 'text-gray-700', 'button' => 'bg-gray-800 hover:bg-gray-900 text-white', 'dot' => 'bg-gray-500'],
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
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Ver guias</a>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-3 sm:p-5 lg:p-6">
        <section id="estado-dia" class="scroll-mt-24 mb-3 rounded-lg border p-3 shadow-sm sm:p-4 {{ $modeTone['panel'] }}">
            <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $modeTone['badge'] }}">{{ $modeContent['label'] }}</span>
                    <h2 class="mt-2 text-base font-black {{ $modeTone['title'] }} te-mode-title sm:text-lg">{{ $modeContent['title'] }}</h2>
                    <p class="mt-0.5 max-w-3xl text-xs font-semibold {{ $modeTone['text'] }}">{{ $modeContent['description'] }}</p>
                    <p class="mt-2 text-xs text-gray-700">{{ $assistantMessage }}</p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <span class="te-count-pill rounded-lg border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-black text-gray-700">{{ $summary['total'] }} pendiente(s)</span>
                        <span class="te-count-pill rounded-lg border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-black {{ $summary['urgent'] > 0 ? 'text-red-700 te-count-pill-danger' : 'text-gray-700' }}">{{ $summary['urgent'] }} urgente(s)</span>
                    </div>
                </div>
            </div>
        </section>

        @if ($cards->isNotEmpty())
            <section id="tareas" class="scroll-mt-24 mt-3">
                <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-500">Accesos directos</p>
                        <h3 class="text-sm font-black text-gray-950 sm:text-base">Gestiona cada tarea desde Mis Guias</h3>
                    </div>
                    <a href="{{ route('shipments.index') }}" class="text-xs font-bold text-blue-700 hover:text-blue-800">Ver todas las guias</a>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($cards as $card)
                        @php $tone = $toneClasses[$card['tone']] ?? $toneClasses['slate']; @endphp
                        <a href="{{ $card['route'] }}" class="group rounded-lg border bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $card['count'] > 0 ? $tone['panel'] : 'border-gray-200' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-black {{ $tone['badge'] }}">{{ $card['priority'] }}</span>
                                    <h4 class="mt-1 text-sm font-black text-gray-950">{{ $card['title'] }}</h4>
                                    <p class="mt-0.5 text-xs text-gray-600">{{ $card['description'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-lg {{ $card['count'] > 0 ? $tone['countPill'] : 'bg-gray-100 text-gray-500' }} px-2.5 py-1 text-lg font-black">{{ $card['count'] }}</span>
                            </div>
                            <span class="mt-2 inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-bold {{ $card['count'] > 0 ? $tone['button'] : 'border border-gray-300 bg-white text-gray-700' }}">
                                {{ $card['action'] }} en Mis Guias
                                <span class="transition group-hover:translate-x-0.5">-&gt;</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm">
                    <a href="{{ $startUrl }}" class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-xs font-bold shadow-sm {{ $modeTone['button'] }}">Empezar mi dia</a>
                    <button type="button" id="copy-daily-summary" data-summary="{{ $summaryText }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50">Copiar reporte del dia</button>
                    <span id="daily-summary-copy-status" class="text-xs font-bold text-emerald-700" aria-live="polite"></span>
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
