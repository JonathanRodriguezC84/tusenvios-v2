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
    $goalTone = $toneClasses[$dailyGoal['tone']] ?? $toneClasses['blue'];
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
                    <div class="mt-4 grid gap-2 sm:grid-cols-3">
                        <span class="rounded-lg border border-white/70 bg-white/80 px-3 py-2 text-xs font-black text-gray-700">{{ $summary['total'] }} pendiente(s)</span>
                        <span class="rounded-lg border border-white/70 bg-white/80 px-3 py-2 text-xs font-black {{ $summary['urgent'] > 0 ? 'text-red-700' : 'text-gray-700' }}">{{ $summary['urgent'] }} urgente(s)</span>
                        <span class="rounded-lg border border-white/70 bg-white/80 px-3 py-2 text-xs font-black text-gray-700">{{ $dailyGoal['score'] }}/100 salud</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center xl:justify-end">
                    <a href="{{ $startUrl }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-bold shadow-sm {{ $modeTone['button'] }}">Empezar mi dia</a>
                    <button type="button" id="copy-daily-summary" data-summary="{{ $summaryText }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">Copiar reporte del dia</button>
                    <span id="daily-summary-copy-status" class="min-h-5 text-xs font-bold text-emerald-700" aria-live="polite"></span>
                </div>
            </div>
        </section>

        <details class="mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Contexto del dia</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $dailyGoal['score'] }}/100</span>
            </summary>
            <div class="mt-4 space-y-3">
        <details id="plan-dia" class="scroll-mt-24 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver plan recomendado</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $dailyPlan['estimatedMinutes'] > 0 ? $dailyPlan['estimatedMinutes'].' min' : 'Opcional' }}</span>
            </summary>
            <div class="mt-4">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-gray-700">{{ $dailyPlan['focus']['label'] }}</span>
                    <h2 class="mt-3 text-xl font-black text-gray-950">{{ $dailyPlan['focus']['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">{{ $dailyPlan['focus']['detail'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-800">{{ $dailyPlan['resolvedToday'] }} avance(s) hoy</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">
                            {{ $dailyPlan['estimatedMinutes'] > 0 ? $dailyPlan['estimatedMinutes'].' min estimados' : 'Dia despejado' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="relative grid h-24 w-24 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#2563eb {{ $dailyPlan['progress'] }}%, #e5e7eb 0);">
                        <div class="grid h-16 w-16 place-items-center rounded-full bg-white shadow-sm">
                            <span class="text-lg font-black text-gray-950">{{ $dailyPlan['progress'] }}%</span>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Avance operativo</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">Mantiene visible que tanto se ha movido el dia.</p>
                        <a href="{{ $dailyPlan['startUrl'] }}" class="mt-3 inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Continuar</a>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-3 lg:grid-cols-3">
                @foreach ($dailyPlan['steps'] as $step)
                    @php $stepTone = $toneClasses[$step['tone']] ?? $toneClasses['slate']; @endphp
                    <a href="{{ $step['route'] }}" class="group rounded-lg border bg-gray-50 p-4 transition hover:-translate-y-0.5 hover:shadow-sm {{ $stepTone['panel'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $stepTone['badge'] }}">
                                    {{ $step['count'] > 0 ? $step['count'].' guia(s)' : 'Sugerido' }}
                                </span>
                                <h3 class="mt-3 text-sm font-black text-gray-950">{{ $loop->iteration }}. {{ $step['title'] }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs font-semibold text-gray-600">{{ $step['detail'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-white px-2 py-1 text-xs font-black text-gray-700 shadow-sm">Ir</span>
                        </div>
                        <p class="mt-3 text-xs font-black {{ $stepTone['text'] }}">{{ $step['action'] }}</p>
                    </a>
                @endforeach
            </div>
            </div>
        </details>

        <details id="meta-dia" class="scroll-mt-24 rounded-lg border bg-white p-5 shadow-sm {{ $goalTone['panel'] }}">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver meta y score operativo</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm">{{ $dailyGoal['score'] }}/100</span>
            </summary>
            <div class="mt-4">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px] xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black uppercase tracking-wider {{ $goalTone['badge'] }}">Meta del dia</span>
                        <span class="inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-black text-gray-700 shadow-sm">{{ $dailyGoal['label'] }}</span>
                    </div>
                    <h2 class="mt-3 text-xl font-black text-gray-950">Score operativo: {{ $dailyGoal['score'] }}/100</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-700">{{ $dailyGoal['description'] }}</p>

                    <div class="mt-5 rounded-lg border border-white/70 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Prioridad que mas ayuda</p>
                                <h3 class="mt-1 text-base font-black text-gray-950">{{ $dailyGoal['priority']['title'] }}</h3>
                                <p class="mt-1 text-sm font-semibold text-gray-600">{{ $dailyGoal['priority']['detail'] }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                                <span class="inline-flex justify-center rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $dailyGoal['priority']['impact'] }}</span>
                                <a href="{{ $dailyGoal['priority']['route'] }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm {{ $goalTone['button'] }}">{{ $dailyGoal['priority']['action'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-white/70 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="relative grid h-28 w-28 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#111827 {{ $dailyGoal['score'] }}%, #e5e7eb 0);">
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white shadow-sm">
                                <div class="text-center">
                                    <p class="text-2xl font-black text-gray-950">{{ $dailyGoal['score'] }}</p>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-500">puntos</p>
                                </div>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pulso del dia</p>
                            <div class="mt-3 h-2 rounded-full bg-gray-200">
                                <div class="h-2 rounded-full bg-gray-900" style="width: {{ $dailyGoal['score'] }}%"></div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                @foreach ($dailyGoal['metrics'] as $metric)
                                    <div class="min-w-0 rounded-md border border-gray-200 bg-gray-50 px-3 py-2">
                                        <p class="truncate text-[10px] font-black uppercase tracking-wider text-gray-500">{{ $metric['label'] }}</p>
                                        <p class="mt-1 text-lg font-black text-gray-950">{{ $metric['value'] }}</p>
                                        <p class="truncate text-[11px] font-semibold text-gray-500">{{ $metric['detail'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </details>
            </div>
        </details>

        <section id="rutina-express" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-focus-routine data-storage-key="{{ $focusRoutine['storageKey'] }}">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_260px] xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-gray-700">Rutina express</span>
                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black text-blue-800">{{ $focusRoutine['totalMinutes'] }} min sugeridos</span>
                    </div>
                    <h2 class="mt-3 text-xl font-black text-gray-950">Completa lo esencial sin perder tiempo</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">Marca cada paso mientras trabajas. El avance queda guardado para hoy en este navegador.</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Avance de rutina</p>
                            <p class="mt-1 text-2xl font-black text-gray-950"><span data-routine-done>0</span>/{{ count($focusRoutine['steps']) }}</p>
                        </div>
                        <span class="text-sm font-black text-blue-700" data-routine-progress>0%</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-200">
                        <div class="h-2 rounded-full bg-blue-700 transition-all" data-routine-bar style="width: 0%"></div>
                    </div>
                    <p class="mt-3 min-h-5 text-xs font-bold text-emerald-700" data-routine-status aria-live="polite"></p>
                </div>
            </div>

            <div class="mt-5 grid gap-3 lg:grid-cols-3">
                @foreach ($focusRoutine['steps'] as $step)
                    <article class="rounded-lg border border-gray-200 bg-gray-50 p-4" data-routine-step>
                        <div class="flex items-start gap-3">
                            <input id="routine-step-{{ $step['key'] }}" type="checkbox" value="{{ $step['key'] }}" class="routine-check mt-1 h-5 w-5 rounded border-gray-300 text-blue-700 focus:ring-blue-700">
                            <div class="min-w-0 flex-1">
                                <label for="routine-step-{{ $step['key'] }}" class="block cursor-pointer text-sm font-black text-gray-950">{{ $loop->iteration }}. {{ $step['label'] }}</label>
                                <p class="mt-1 text-xs font-semibold text-gray-600">{{ $step['detail'] }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-gray-700 shadow-sm">{{ $step['minutes'] }} min</span>
                                    <a href="{{ $step['route'] }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-black text-gray-700 hover:bg-gray-50">{{ $step['action'] }}</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <details class="mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Herramientas opcionales</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">Abrir si las necesitas</span>
            </summary>
            <div class="mt-4 space-y-3">
        @if ($opportunityRadar['count'] > 0)
            <details id="oportunidades" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Ver oportunidades comerciales</span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-800">{{ $opportunityRadar['count'] }}</span>
                </summary>
                <div class="mt-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-emerald-800">Radar de oportunidades</span>
                        <h2 class="mt-3 text-xl font-black text-gray-950">Acciones pequenas que hacen ver mas profesional tu negocio</h2>
                        <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">Usa estos accesos cuando la operacion ya esta avanzando y quieres convertir orden en confianza, recompra o velocidad.</p>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider text-gray-500">{{ $opportunityRadar['count'] }} oportunidad(es)</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($opportunityRadar['items'] as $opportunity)
                        @php $opportunityTone = $toneClasses[$opportunity['tone']] ?? $toneClasses['slate']; @endphp
                        <a href="{{ $opportunity['route'] }}" class="group rounded-lg border p-4 transition hover:-translate-y-0.5 hover:shadow-sm {{ $opportunityTone['panel'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $opportunityTone['badge'] }}">{{ $opportunity['metric'] }}</span>
                                    <h3 class="mt-3 text-sm font-black text-gray-950">{{ $opportunity['title'] }}</h3>
                                    <p class="mt-1 line-clamp-3 text-xs font-semibold text-gray-600">{{ $opportunity['detail'] }}</p>
                                </div>
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $opportunityTone['dot'] }}"></span>
                            </div>
                            <p class="mt-4 text-xs font-black {{ $opportunityTone['text'] }}">{{ $opportunity['action'] }}</p>
                        </a>
                    @endforeach
                </div>
                </div>
            </details>
        @endif

        <details id="mensajes" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver mensajes para clientes</span>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-800">{{ $messageCenter['count'] }}</span>
            </summary>
            <div class="mt-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-blue-800">Centro de mensajes</span>
                    <h2 class="mt-3 text-xl font-black text-gray-950">Textos profesionales listos para copiar</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">Mensajes simples para atender mejor al cliente sin activar integraciones pagas.</p>
                </div>
                <span id="message-template-copy-status" class="min-h-5 text-xs font-bold text-emerald-700" aria-live="polite"></span>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($messageCenter['items'] as $template)
                    @php $templateTone = $toneClasses[$template['tone']] ?? $toneClasses['slate']; @endphp
                    <article class="rounded-lg border p-4 {{ $templateTone['panel'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $templateTone['badge'] }}">{{ $template['label'] }}</span>
                                <h3 class="mt-3 text-sm font-black text-gray-950">{{ $template['title'] }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs font-semibold text-gray-600">{{ $template['detail'] }}</p>
                            </div>
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $templateTone['dot'] }}"></span>
                        </div>
                        <div class="mt-4 rounded-md border border-white/70 bg-white px-3 py-2">
                            <p class="line-clamp-3 text-xs font-semibold text-gray-600">{{ $template['message'] }}</p>
                        </div>
                        <button type="button" data-message="{{ $template['message'] }}" class="copy-message-template mt-3 inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar texto</button>
                    </article>
                @endforeach
            </div>
            </div>
        </details>

        <details id="resumen-dia" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver resumen numerico</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $summary['total'] }} pendiente(s)</span>
            </summary>
            <section class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes de hoy</p>
                <p class="mt-1 text-3xl font-black text-gray-950">{{ $summary['total'] }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-red-700">Atencion prioritaria</p>
                <p class="mt-1 text-3xl font-black text-red-800">{{ $summary['urgent'] }}</p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Impresas hoy</p>
                <p class="mt-1 text-3xl font-black text-blue-800">{{ $summary['printedToday'] }}</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">Entregadas hoy</p>
                <p class="mt-1 text-3xl font-black text-emerald-800">{{ $summary['deliveredToday'] }}</p>
            </div>
            </section>
        </details>

        <details id="cierre-dia" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver cierre del dia</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $closingChecklist['progress'] }}%</span>
            </summary>
            <div class="mt-4">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-gray-700">Cierre del dia</span>
                    <h3 class="mt-2 text-lg font-black text-gray-950">Deja tu operacion lista antes de terminar</h3>
                    <p class="mt-1 text-sm font-semibold text-gray-600">{{ $closingChecklist['pending'] > 0 ? $closingChecklist['pending'].' punto(s) necesitan atencion antes de cerrar.' : 'Todo esta en orden para cerrar la jornada.' }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" id="copy-closing-report" data-report="{{ $closingReportText }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar cierre</button>
                        <span id="closing-report-copy-status" class="min-h-5 text-xs font-bold text-emerald-700" aria-live="polite"></span>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Avance de cierre</p>
                            <p class="mt-1 text-2xl font-black text-gray-950">{{ $closingChecklist['done'] }}/{{ $closingChecklist['total'] }}</p>
                        </div>
                        <span class="text-sm font-black text-blue-700">{{ $closingChecklist['progress'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-200">
                        <div class="h-2 rounded-full bg-blue-700" style="width: {{ $closingChecklist['progress'] }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($closingChecklist['items'] as $item)
                    <a href="{{ $item['route'] }}" class="rounded-lg border p-4 transition hover:-translate-y-0.5 hover:shadow-sm {{ $item['done'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full text-[10px] font-black {{ $item['done'] ? 'bg-emerald-700 text-white' : 'bg-amber-500 text-white' }}">{{ $item['done'] ? 'OK' : '!' }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-gray-950">{{ $item['label'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-600">{{ $item['detail'] }}</p>
                                <p class="mt-3 text-xs font-black {{ $item['done'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item['done'] ? 'Listo' : 'Revisar' }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            </div>
        </details>

        @if ($issueAssistant['count'] > 0)
            <details id="novedades" class="scroll-mt-24 mb-5 rounded-lg border border-red-200 bg-white p-5 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-red-950">
                    <span>Ver asistente de novedades</span>
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-800">{{ $issueAssistant['count'] }}</span>
                </summary>
                <section class="mt-4 rounded-lg border border-red-100 bg-white shadow-sm">
                <div class="border-b border-red-100 bg-red-50 p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-black uppercase tracking-wider text-red-800">Asistente de novedades</span>
                            <h3 class="mt-2 text-lg font-black text-red-950">Resuelve primero las guias que pueden frustrar al cliente</h3>
                            <p class="mt-1 max-w-3xl text-sm font-semibold text-red-800">Estas guias necesitan confirmacion, reprogramacion o cierre de devolucion. Cada una trae una accion recomendada y un mensaje listo para copiar.</p>
                        </div>
                        <a href="{{ route('shipments.index', ['status' => 'failed_delivery']) }}" class="inline-flex items-center justify-center rounded-lg bg-red-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-red-800">Ver novedades</a>
                    </div>
                </div>

                <div class="grid gap-3 p-5 xl:grid-cols-2">
                    @foreach ($issueAssistant['shipments'] as $issue)
                        <article class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm font-black text-gray-950">{{ $issue['guide'] }}</span>
                                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-black text-red-700">{{ $statusLabels[$issue['status']] ?? $issue['status'] }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-sm font-black text-gray-900">{{ $issue['recipient'] ?: 'Cliente sin nombre' }}</p>
                                    <p class="text-xs font-semibold text-gray-500">
                                        {{ $issue['phone'] ?: 'Sin telefono' }}
                                        @if ($issue['city'])
                                            - {{ $issue['city'] }}
                                        @endif
                                    </p>
                                    @if ($issue['reason'])
                                        <p class="mt-2 rounded-md border border-red-100 bg-white px-3 py-2 text-xs font-semibold text-red-800">{{ $issue['reason'] }}</p>
                                    @endif
                                </div>
                                <a href="{{ $issue['route'] }}" class="shrink-0 rounded-lg bg-blue-700 px-3 py-2 text-xs font-black text-white hover:bg-blue-800">Abrir guia</a>
                            </div>

                            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                @foreach ($issue['nextSteps'] as $step)
                                    <div class="rounded-md border border-gray-200 bg-white px-3 py-2">
                                        <p class="text-xs font-black text-gray-800">{{ $loop->iteration }}. {{ $step }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                <button type="button" data-message="{{ $issue['message'] }}" class="copy-issue-message inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar mensaje</button>
                                <a href="{{ $issue['tracking'] }}" target="_blank" class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Ver seguimiento</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                </section>
            </details>
        @endif

        @if ($customerAttention['count'] > 0)
            <details id="clientes" class="scroll-mt-24 mb-5 rounded-lg border border-blue-200 bg-white p-5 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-blue-950">
                    <span>Ver clientes que necesitan atencion</span>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800">{{ $customerAttention['count'] }}</span>
                </summary>
                <section class="mt-4 rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-b border-blue-100 bg-blue-50 p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <span class="inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-black uppercase tracking-wider text-blue-800">Clientes que necesitan atencion</span>
                            <h3 class="mt-2 text-lg font-black text-blue-950">Convierte entregas y clientes repetidos en buena atencion</h3>
                            <p class="mt-1 max-w-3xl text-sm font-semibold text-blue-800">Mensajes listos para seguimiento postventa o para clientes que ya han comprado mas de una vez.</p>
                        </div>
                        <a href="{{ route('recipients.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Ver clientes</a>
                    </div>
                </div>

                <div class="grid gap-3 p-5 xl:grid-cols-3">
                    @foreach ($customerAttention['items'] as $customer)
                        @php
                            $customerTone = $customer['tone'] === 'emerald'
                                ? ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'button' => 'bg-emerald-700 hover:bg-emerald-800']
                                : ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'button' => 'bg-blue-700 hover:bg-blue-800'];
                        @endphp
                        <article class="rounded-lg border p-4 {{ $customerTone['panel'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black uppercase tracking-wider {{ $customerTone['badge'] }}">{{ $customer['label'] }}</span>
                                    <h4 class="mt-3 truncate text-sm font-black text-gray-950">{{ $customer['title'] }}</h4>
                                    <p class="mt-1 text-xs font-semibold text-gray-600">{{ $customer['detail'] }}</p>
                                    <p class="mt-2 truncate text-xs font-bold text-gray-500">
                                        {{ $customer['phone'] ?: 'Sin telefono' }}
                                        @if ($customer['city'])
                                            - {{ $customer['city'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                <button type="button" data-message="{{ $customer['message'] }}" class="copy-customer-attention-message inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar mensaje</button>
                                <a href="{{ $customer['route'] }}" class="inline-flex flex-1 items-center justify-center rounded-lg px-3 py-2 text-xs font-black text-white shadow-sm {{ $customerTone['button'] }}">{{ $customer['action'] }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                </section>
            </details>
        @endif

        @if ($dailyMode === 'all_clear')
            <details id="siguientes-pasos" class="scroll-mt-24 mb-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Ver siguientes pasos opcionales</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">Opcional</span>
                </summary>
                <section class="mt-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Siguientes pasos sugeridos</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Aprovecha que la operacion esta tranquila</h3>
                    </div>
                    <a href="{{ route('shipments.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver historial</a>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($quickActions as $action)
                        @if ($action['show'])
                            <a href="{{ $action['route'] }}" class="rounded-lg border border-gray-200 bg-gray-50 p-4 hover:border-blue-200 hover:bg-blue-50">
                                <p class="text-sm font-black text-gray-950">{{ $action['label'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-600">{{ $action['description'] }}</p>
                            </a>
                        @endif
                    @endforeach
                </div>
                </section>
            </details>
        @endif
            </div>
        </details>

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
        const focusRoutine = document.querySelector('[data-focus-routine]');

        if (focusRoutine) {
            const storageKey = focusRoutine.dataset.storageKey || 'daily-focus';
            const checks = Array.from(focusRoutine.querySelectorAll('.routine-check'));
            const doneLabel = focusRoutine.querySelector('[data-routine-done]');
            const progressLabel = focusRoutine.querySelector('[data-routine-progress]');
            const progressBar = focusRoutine.querySelector('[data-routine-bar]');
            const statusLabel = focusRoutine.querySelector('[data-routine-status]');

            const readRoutineState = () => {
                try {
                    return JSON.parse(window.localStorage.getItem(storageKey) || '[]');
                } catch (error) {
                    return [];
                }
            };

            const writeRoutineState = (checkedValues) => {
                window.localStorage.setItem(storageKey, JSON.stringify(checkedValues));
            };

            const updateRoutineProgress = (announce = false) => {
                const checkedValues = checks
                    .filter((check) => check.checked)
                    .map((check) => check.value);
                const total = Math.max(1, checks.length);
                const percent = Math.round((checkedValues.length / total) * 100);

                if (doneLabel) {
                    doneLabel.textContent = checkedValues.length;
                }

                if (progressLabel) {
                    progressLabel.textContent = `${percent}%`;
                }

                if (progressBar) {
                    progressBar.style.width = `${percent}%`;
                }

                writeRoutineState(checkedValues);

                if (announce && statusLabel) {
                    statusLabel.textContent = percent === 100 ? 'Rutina completada' : 'Avance guardado';
                    window.setTimeout(() => {
                        statusLabel.textContent = '';
                    }, 2200);
                }
            };

            const savedChecks = readRoutineState();
            checks.forEach((check) => {
                check.checked = savedChecks.includes(check.value);
                check.addEventListener('change', () => updateRoutineProgress(true));
            });
            updateRoutineProgress(false);
        }

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

        const copyClosingReport = document.getElementById('copy-closing-report');

        if (copyClosingReport) {
            copyClosingReport.addEventListener('click', async () => {
                const status = document.getElementById('closing-report-copy-status');
                const text = copyClosingReport.dataset.report || '';

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
                    status.textContent = 'Cierre copiado';
                    window.setTimeout(() => {
                        status.textContent = '';
                    }, 2500);
                }
            });
        }

        document.querySelectorAll('.copy-issue-message').forEach((button) => {
            button.addEventListener('click', async () => {
                const original = button.textContent;
                const text = button.dataset.message || '';

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

                button.textContent = 'Mensaje copiado';
                button.disabled = true;
                window.setTimeout(() => {
                    button.textContent = original;
                    button.disabled = false;
                }, 2000);
            });
        });

        document.querySelectorAll('.copy-message-template').forEach((button) => {
            button.addEventListener('click', async () => {
                const original = button.textContent;
                const status = document.getElementById('message-template-copy-status');
                const text = button.dataset.message || '';

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

                button.textContent = 'Texto copiado';
                button.disabled = true;

                if (status) {
                    status.textContent = 'Texto copiado';
                }

                window.setTimeout(() => {
                    button.textContent = original;
                    button.disabled = false;

                    if (status) {
                        status.textContent = '';
                    }
                }, 2000);
            });
        });

        document.querySelectorAll('.copy-customer-attention-message').forEach((button) => {
            button.addEventListener('click', async () => {
                const original = button.textContent;
                const text = button.dataset.message || '';

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

                button.textContent = 'Mensaje copiado';
                button.disabled = true;
                window.setTimeout(() => {
                    button.textContent = original;
                    button.disabled = false;
                }, 2000);
            });
        });
    </script>
</x-app-layout>
