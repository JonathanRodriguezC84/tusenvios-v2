@php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $healthTone = [
        'emerald' => ['ring' => '#059669', 'soft' => 'bg-emerald-50 border-emerald-200', 'text' => 'text-emerald-800', 'bar' => 'bg-emerald-500'],
        'blue' => ['ring' => '#2563eb', 'soft' => 'bg-blue-50 border-blue-200', 'text' => 'text-blue-800', 'bar' => 'bg-blue-600'],
        'amber' => ['ring' => '#d97706', 'soft' => 'bg-amber-50 border-amber-200', 'text' => 'text-amber-800', 'bar' => 'bg-amber-500'],
        'red' => ['ring' => '#dc2626', 'soft' => 'bg-red-50 border-red-200', 'text' => 'text-red-800', 'bar' => 'bg-red-600'],
    ][$operationHealth['tone']] ?? ['ring' => '#2563eb', 'soft' => 'bg-blue-50 border-blue-200', 'text' => 'text-blue-800', 'bar' => 'bg-blue-600'];

    $deliveryTone = $deliveryRate['total'] === 0 ? '#9ca3af' : ($deliveryRate['rate'] >= 80 ? '#059669' : ($deliveryRate['rate'] >= 50 ? '#d97706' : '#dc2626'));
    $deliveryRingValue = $deliveryRate['total'] === 0 ? 100 : $deliveryRate['rate'];
    $pendingTotal = $metrics['pending_print'] + $metrics['warehouse'] + $metrics['issues'] + $metrics['return_pending'];
    $moneyTotal = (float) $metrics['revenue_today'];
    $statusTotal = max(1, $chartStatusDistribution['total'] ?? 1);
    $workdayTone = [
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-800', 'bar' => 'bg-emerald-600', 'button' => 'bg-emerald-700 hover:bg-emerald-800 text-white'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'bar' => 'bg-blue-700', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white'],
        'red' => ['panel' => 'border-red-200 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'text' => 'text-red-800', 'bar' => 'bg-red-700', 'button' => 'bg-red-700 hover:bg-red-800 text-white'],
    ][$workdaySummary['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'bar' => 'bg-blue-700', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white'];
    $professionalTone = $professionalScore['score'] >= 85
        ? ['ring' => '#059669', 'bar' => 'bg-emerald-600', 'panel' => 'border-emerald-200 bg-emerald-50', 'text' => 'text-emerald-800']
        : ($professionalScore['score'] >= 60
            ? ['ring' => '#2563eb', 'bar' => 'bg-blue-700', 'panel' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-800']
            : ['ring' => '#d97706', 'bar' => 'bg-amber-500', 'panel' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-800']);
    $moneyTone = [
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-800', 'bar' => 'bg-emerald-600'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'text' => 'text-amber-800', 'bar' => 'bg-amber-500'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'bar' => 'bg-blue-700'],
    ][$moneySummary['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'bar' => 'bg-blue-700'];
    $priorityTone = [
        'red' => ['panel' => 'border-red-200 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'text' => 'text-red-800', 'button' => 'bg-red-700 hover:bg-red-800 text-white', 'dot' => 'bg-red-500'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white', 'dot' => 'bg-blue-500'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'text' => 'text-amber-800', 'button' => 'bg-amber-600 hover:bg-amber-700 text-white', 'dot' => 'bg-amber-500'],
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-800', 'button' => 'bg-emerald-700 hover:bg-emerald-800 text-white', 'dot' => 'bg-emerald-500'],
    ][$todayPriority['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800', 'button' => 'bg-blue-700 hover:bg-blue-800 text-white', 'dot' => 'bg-blue-500'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" description="Tu centro de control para vender, preparar y entregar mejor.">
            <x-slot name="eyebrow">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY') }}</x-slot>
            <x-slot name="actions">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <select name="range" id="dash-range" class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <option value="today" {{ $dateRange['range'] === 'today' ? 'selected' : '' }}>Hoy</option>
                            <option value="7d" {{ $dateRange['range'] === '7d' ? 'selected' : '' }}>Ultimos 7 dias</option>
                            <option value="30d" {{ $dateRange['range'] === '30d' ? 'selected' : '' }}>Ultimos 30 dias</option>
                            <option value="90d" {{ $dateRange['range'] === '90d' ? 'selected' : '' }}>Ultimos 90 dias</option>
                            <option value="custom" {{ !in_array($dateRange['range'], ['today','7d','30d','90d']) ? 'selected' : '' }}>Personalizado</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 4 4-4" /></svg>
                    </div>
                    <div id="dash-dates" class="hidden items-center gap-2">
                        <input type="date" name="from" value="{{ $dateRange['from'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <span class="text-sm text-gray-500">a</span>
                        <input type="date" name="to" value="{{ $dateRange['to'] }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Aplicar</button>
                </form>
                @if (Auth::user()->canCreateShipments())
                    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">
        @if ($onboarding['show'])
            <section class="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Primeros pasos</p>
                        <h2 class="mt-1 text-base font-black text-gray-950">Completa la base profesional de tu negocio</h2>
                    </div>
                    <div class="min-w-40">
                        <div class="h-2 rounded-full bg-white">
                            <div class="h-2 rounded-full bg-blue-700" style="width: {{ round(($onboarding['completed'] / max(1, $onboarding['total'])) * 100) }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs font-bold text-blue-800">{{ $onboarding['completed'] }}/{{ $onboarding['total'] }} completo</p>
                    </div>
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    @foreach ($onboarding['steps'] as $step)
                        <a href="{{ $step['route'] }}" class="rounded-lg border {{ $step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white' }} p-3 hover:border-blue-300">
                            <p class="text-sm font-black text-gray-950">{{ $step['done'] ? 'Listo' : $loop->iteration.'.' }} {{ $step['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-gray-600">{{ $step['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($trialGuideCounter)
            <section class="mb-5 rounded-lg border border-blue-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Prueba gratis</p>
                        <h2 class="mt-1 text-base font-black text-gray-950">Te quedan {{ $trialGuideCounter['remaining'] }} de {{ $trialGuideCounter['total'] }} guias</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-3 w-48 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full rounded-full bg-blue-700" style="width:{{ ($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / max(1, $trialGuideCounter['total']) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-black text-blue-800">{{ $trialGuideCounter['remaining'] }}/{{ $trialGuideCounter['total'] }}</span>
                    </div>
                </div>
            </section>
        @endif

        <section class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $priorityTone['badge'] }}">{{ $todayPriority['label'] }}</span>
                    <h2 class="mt-3 text-lg font-black text-gray-950">Empieza por esto: {{ $todayPriority['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">{{ $todayPriority['description'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $todayPriority['route'] }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm {{ $priorityTone['button'] }}">{{ $todayPriority['action'] }}</a>
                    <a href="{{ route('daily-tasks.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Ver tareas</a>
                </div>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-3">
                @foreach ($todayPriority['steps'] as $step)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Paso {{ $loop->iteration }}</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">{{ $step }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('daily-tasks.index') }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                    <p class="mt-1 text-2xl font-black text-gray-950">{{ $pendingTotal }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'failed_delivery']) }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-red-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                    <p class="mt-1 text-2xl font-black {{ $metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $metrics['issues'] }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'created']) }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Por imprimir</p>
                    <p class="mt-1 text-2xl font-black text-gray-950">{{ $metrics['pending_print'] }}</p>
                </a>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregadas</p>
                    <p class="mt-1 text-2xl font-black text-emerald-700">{{ $metrics['delivered_today'] }}</p>
                </div>
            </div>
        </section>

        <details class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver mas detalles solo si los necesitas</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">Opcional</span>
            </summary>
            <div class="mt-5">

        @if ($professionalScore['show'])
            <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Base profesional de tu marca</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $professionalScore['score'] }}%</span>
                </summary>
            <section class="mt-4">
                <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)] xl:items-center">
                    <div class="flex justify-center">
                        <div class="relative grid h-40 w-40 place-items-center rounded-full" style="background: conic-gradient({{ $professionalTone['ring'] }} {{ $professionalScore['score'] }}%, #e5e7eb 0);">
                            <div class="grid h-28 w-28 place-items-center rounded-full bg-white shadow-inner">
                                <div class="text-center">
                                    <p class="text-3xl font-black text-gray-950">{{ $professionalScore['score'] }}%</p>
                                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">marca</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wider {{ $professionalTone['panel'] }} {{ $professionalTone['text'] }}">{{ $professionalScore['label'] }}</span>
                                <h2 class="mt-3 text-xl font-black text-gray-950">Score de Profesionalismo</h2>
                                <p class="mt-1 max-w-2xl text-sm font-semibold text-gray-600">
                                    Completa estos puntos para que tu negocio se vea mas confiable en etiquetas, seguimiento y comunicacion con clientes.
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-black text-gray-950">{{ $professionalScore['completed'] }}/{{ $professionalScore['total'] }} listo</p>
                                <p class="text-xs font-semibold text-gray-500">Base comercial</p>
                            </div>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $professionalTone['bar'] }}" style="width: {{ $professionalScore['score'] }}%"></div>
                        </div>
                        <div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                            @foreach (collect($professionalScore['steps'])->where('done', false)->take(3) as $step)
                                <a href="{{ $step['route'] }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:border-blue-300 hover:bg-white">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-black text-gray-950">{{ $step['label'] }}</p>
                                        <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-blue-700">{{ $step['action'] }}</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-600">{{ $step['description'] }}</p>
                                </a>
                            @endforeach
                            @if (collect($professionalScore['steps'])->where('done', false)->isEmpty())
                                <a href="{{ route('shipments.index') }}" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 hover:bg-white">
                                    <p class="text-sm font-black text-emerald-900">Todo listo</p>
                                    <p class="mt-1 text-xs font-semibold text-emerald-800">Tu marca ya tiene una base profesional completa.</p>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            </details>
        @endif

        <details class="mb-5 rounded-lg border p-4 shadow-sm {{ $workdayTone['panel'] }}">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Salud operativa</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm">{{ $workdaySummary['progress'] }}%</span>
            </summary>
        <section class="mt-4">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $workdayTone['badge'] }}">{{ $workdaySummary['label'] }}</span>
                    <h2 class="mt-3 text-xl font-black text-gray-950">{{ $workdaySummary['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold {{ $workdayTone['text'] }}">{{ $workdaySummary['description'] }}</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                            <p class="mt-1 text-2xl font-black text-gray-950">{{ $pendingTotal }}</p>
                        </div>
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                            <p class="mt-1 text-2xl font-black {{ $metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $metrics['issues'] }}</p>
                        </div>
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Quietas</p>
                            <p class="mt-1 text-2xl font-black {{ $operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950' }}">{{ $operationHealth['stale'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Avance operativo</p>
                            <p class="mt-1 text-3xl font-black text-gray-950">{{ $workdaySummary['progress'] }}%</p>
                        </div>
                        <a href="{{ route('daily-tasks.index') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm {{ $workdayTone['button'] }}">Abrir Tareas Diarias</a>
                    </div>
                    <div class="mt-4 h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full {{ $workdayTone['bar'] }}" style="width: {{ $workdaySummary['progress'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-gray-500">Este indicador baja cuando hay novedades, guias sin imprimir o guias quietas.</p>
                </div>
            </div>
        </section>
        </details>

        <section class="mb-4 rounded-lg border p-4 shadow-sm {{ $priorityTone['panel'] }}">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_240px] xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $priorityTone['badge'] }}">{{ $todayPriority['label'] }}</span>
                        <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm">{{ $todayPriority['metric'] }} {{ $todayPriority['metricLabel'] }}</span>
                    </div>
                    <h2 class="mt-3 text-lg font-black text-gray-950">{{ $todayPriority['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold {{ $priorityTone['text'] }}">{{ $todayPriority['description'] }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ $todayPriority['route'] }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm {{ $priorityTone['button'] }}">{{ $todayPriority['action'] }}</a>
                        <a href="{{ route('daily-tasks.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Abrir Tareas Diarias</a>
                        <button type="button" id="copy-dashboard-report" data-report="{{ $dashboardReportText }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar reporte</button>
                        <span id="dashboard-report-copy-status" class="min-h-5 self-center text-xs font-bold text-emerald-700" aria-live="polite"></span>
                    </div>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Plan de accion</p>
                    <div class="mt-3 space-y-2">
                        @foreach ($todayPriority['steps'] as $step)
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $priorityTone['dot'] }}"></span>
                                <p class="text-sm font-bold text-gray-800">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <details class="mb-5 rounded-lg border p-4 shadow-sm {{ $moneyTone['panel'] }}">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Resumen de dinero</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm">${{ number_format($moneySummary['moneyToWatch'], 0, ',', '.') }} a vigilar</span>
            </summary>
        <section class="mt-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $moneyTone['badge'] }}">{{ $moneySummary['label'] }}</span>
                    <h2 class="mt-3 text-xl font-black text-gray-950">Resumen de dinero</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold {{ $moneyTone['text'] }}">
                        Una vista simple para saber que ya se entrego, que esta pendiente de recaudo y que valor merece seguimiento.
                    </p>
                </div>
                <a href="{{ route('shipments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Revisar guias</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Creado en {{ $rangeLabel }}</p>
                    <p class="mt-2 text-2xl font-black text-gray-950">${{ number_format($moneySummary['createdValue'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Valor total de guias no canceladas.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregado</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700">${{ number_format($moneySummary['deliveredValue'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Ingresos asociados a guias entregadas.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Recaudo pendiente</p>
                    <p class="mt-2 text-2xl font-black {{ $moneySummary['collectionOpen'] > 0 ? 'text-amber-700' : 'text-gray-950' }}">${{ number_format($moneySummary['collectionOpen'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Contraentrega abierto sin cierre final.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Dinero a vigilar</p>
                    <p class="mt-2 text-2xl font-black {{ $moneySummary['moneyToWatch'] > 0 ? 'text-red-700' : 'text-gray-950' }}">${{ number_format($moneySummary['moneyToWatch'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Novedades, liquidaciones o recaudos pendientes.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <a href="{{ route('shipments.index', ['status' => 'failed_delivery']) }}" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">En novedades</p>
                    <p class="mt-1 text-lg font-black {{ $moneySummary['issueValue'] > 0 ? 'text-red-700' : 'text-gray-950' }}">${{ number_format($moneySummary['issueValue'], 0, ',', '.') }}</p>
                </a>
                <a href="{{ route('shipments.index') }}" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Por liquidar</p>
                    <p class="mt-1 text-lg font-black {{ $moneySummary['pendingSettlementValue'] > 0 ? 'text-blue-700' : 'text-gray-950' }}">${{ number_format($moneySummary['pendingSettlementValue'], 0, ',', '.') }}</p>
                </a>
                <a href="{{ route('shipments.index', ['status' => 'cancelled']) }}" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Cancelado en {{ $rangeLabel }}</p>
                    <p class="mt-1 text-lg font-black text-gray-950">${{ number_format($moneySummary['cancelledValue'], 0, ',', '.') }}</p>
                </a>
            </div>
        </section>
        </details>

        @if (! empty($growthActions))
            <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Acciones para vender y atender mejor</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-800">{{ count($growthActions) }}</span>
                </summary>
            <section class="mt-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Siguiente movimiento</p>
                        <h2 class="mt-1 text-xl font-black text-gray-950">Acciones para vender y atender mejor</h2>
                        <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">Recomendaciones comerciales basadas en tu marca, tus productos y el estado actual de tus guias.</p>
                    </div>
                    @if (Auth::user()->canCreateShipments())
                        <a href="{{ route('shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                    @endif
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    @foreach ($growthActions as $action)
                        @php
                            $growthTone = [
                                'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'text' => 'text-emerald-800', 'badge' => 'bg-emerald-100 text-emerald-800', 'button' => 'text-emerald-800'],
                                'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-800', 'badge' => 'bg-amber-100 text-amber-800', 'button' => 'text-amber-800'],
                                'red' => ['panel' => 'border-red-200 bg-red-50', 'text' => 'text-red-800', 'badge' => 'bg-red-100 text-red-800', 'button' => 'text-red-800'],
                                'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-800', 'button' => 'text-blue-800'],
                            ][$action['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-800', 'button' => 'text-blue-800'];
                        @endphp
                        <a href="{{ $action['route'] }}" class="rounded-lg border p-4 hover:bg-white {{ $growthTone['panel'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-gray-950">{{ $action['label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold {{ $growthTone['text'] }}">{{ $action['description'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-black {{ $growthTone['badge'] }}">{{ $action['metric'] }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">{{ $action['metric_label'] }}</p>
                                <span class="text-xs font-black {{ $growthTone['button'] }}">{{ $action['action'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            </details>
        @endif

        <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Detalle de salud y acciones</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $operationHealth['score'] }}/100</span>
            </summary>
        <section class="mt-4 grid gap-4 xl:grid-cols-[1.35fr_0.9fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="grid gap-5 lg:grid-cols-[220px_1fr] lg:items-center">
                    <div class="flex justify-center">
                        <div class="relative grid h-44 w-44 place-items-center rounded-full" style="background: conic-gradient({{ $healthTone['ring'] }} {{ $operationHealth['score'] }}%, #e5e7eb 0);">
                            <div class="grid h-32 w-32 place-items-center rounded-full bg-white shadow-inner">
                                <div class="text-center">
                                    <p class="text-4xl font-black text-gray-950">{{ $operationHealth['score'] }}</p>
                                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">salud</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wider {{ $healthTone['soft'] }} {{ $healthTone['text'] }}">{{ $operationHealth['label'] }}</span>
                        <h2 class="mt-3 text-2xl font-black text-gray-950">Hoy tu operacion esta bajo control</h2>
                        <p class="mt-2 max-w-2xl text-sm font-semibold text-gray-600">
                            {{ $rangeLabel }}: {{ $metrics['shipments_today'] }} guia(s), {{ $metrics['delivered_today'] }} entregada(s), {{ $pendingTotal }} pendiente(s) operativo(s).
                        </p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <a href="{{ route('daily-tasks.index') }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                                <p class="mt-1 text-2xl font-black text-gray-950">{{ $pendingTotal }}</p>
                            </a>
                            <a href="{{ route('shipments.index', ['status' => 'failed_delivery']) }}" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-red-50">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                                <p class="mt-1 text-2xl font-black {{ $metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950' }}">{{ $metrics['issues'] }}</p>
                            </a>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos</p>
                                <p class="mt-1 text-2xl font-black text-emerald-700">${{ number_format($moneyTotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Siguiente mejor accion</p>
                        <h3 class="mt-1 text-lg font-black text-gray-950">Trabaja por prioridad</h3>
                    </div>
                    <a href="{{ route('daily-tasks.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver tareas</a>
                </div>
                <div class="mt-4 grid gap-3">
                    @foreach (array_slice($smartActions, 0, 4) as $action)
                        @php
                            $actionTone = [
                                'red' => 'border-red-200 bg-red-50 text-red-800',
                                'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
                                'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
                                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                'slate' => 'border-gray-200 bg-gray-50 text-gray-800',
                            ][$action['tone']] ?? 'border-gray-200 bg-gray-50 text-gray-800';
                        @endphp
                        <a href="{{ $action['route'] }}" class="rounded-lg border p-3 hover:bg-white {{ $actionTone }}">
                            <p class="text-sm font-black">{{ $action['label'] }}</p>
                            <p class="mt-1 text-xs font-semibold opacity-80">{{ $action['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </aside>
        </section>
        </details>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregas</p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full" style="background: conic-gradient({{ $deliveryTone }} {{ $deliveryRingValue }}%, #e5e7eb 0);">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-white">
                            <span class="text-center text-sm font-black text-gray-950">{{ $deliveryRate['total'] === 0 ? 'Sin datos' : $deliveryRate['rate'].'%' }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-950">{{ $deliveryRate['delivered'] }} de {{ $deliveryRate['total'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Guias entregadas en {{ $rangeLabel }}</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('shipments.index', ['status' => 'created']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Preparacion</p>
                <p class="mt-2 text-2xl font-black text-gray-950">{{ $metrics['pending_print'] }}</p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-700" style="width: {{ min(100, $metrics['pending_print'] * 12) }}%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Guias por imprimir</p>
            </a>

            <a href="{{ route('shipments.index', ['status' => 'on_route']) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">En movimiento</p>
                <p class="mt-2 text-2xl font-black text-gray-950">{{ $metrics['in_transit'] }}</p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-emerald-600" style="width: {{ min(100, $metrics['in_transit'] * 10) }}%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Guias en ruta o bodega</p>
            </a>

            <a href="{{ route('daily-tasks.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Sin movimiento</p>
                <p class="mt-2 text-2xl font-black {{ $operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950' }}">{{ $operationHealth['stale'] }}</p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-amber-500" style="width: {{ min(100, $operationHealth['stale'] * 18) }}%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Mas de 24 horas quietas</p>
            </a>
        </section>

        @if (! empty($alerts))
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['route'] }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-black shadow-sm hover:bg-white {{ $alert['bg'] }}">
                        <svg class="h-4 w-4 {{ $alert['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $alert['icon'] }}" />
                        </svg>
                        <span>{{ $alert['count'] }} {{ $alert['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <details class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Graficas y analisis</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">{{ $rangeLabel }}</span>
            </summary>
        <section class="mt-4 grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</h3>
                    <span class="shrink-0 text-xs font-bold text-blue-700">{{ $rangeLabel }}</span>
                </div>
                @php $sd = $chartShipmentsByDay; @endphp
                <div class="mt-4 overflow-x-auto pb-2">
                    <div class="flex h-36 min-w-max items-end gap-2">
                        @foreach($sd['days'] as $d)
                            @php $h = max(8, round(($d['count'] / $sd['max']) * 105)); @endphp
                            <div class="flex h-full w-7 shrink-0 flex-col items-center justify-end gap-1">
                                <span class="text-xs font-black text-gray-950">{{ $d['count'] }}</span>
                                <div class="w-full rounded-t-md bg-blue-700" style="height: {{ $h }}px; min-height: 6px;"></div>
                                <span class="text-xs font-bold text-gray-500">{{ $d['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos por entregas</h3>
                    <span class="shrink-0 text-xs font-bold text-emerald-700">{{ $rangeLabel }}</span>
                </div>
                @php $rd = $chartRevenueByDay; @endphp
                <div class="mt-4 overflow-x-auto pb-2">
                    <div class="flex h-36 min-w-max items-end gap-2">
                        @foreach($rd['days'] as $d)
                            @php $h = max(8, round(($d['revenue'] / $rd['max']) * 105)); @endphp
                            <div class="flex h-full w-8 shrink-0 flex-col items-center justify-end gap-1">
                                <span class="max-w-full truncate text-[11px] font-black text-emerald-700">${{ number_format($d['revenue'], 0, ',', '.') }}</span>
                                <div class="w-full rounded-t-md bg-emerald-600" style="height: {{ $h }}px; min-height: 6px;"></div>
                                <span class="text-xs font-bold text-gray-500">{{ $d['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Estado de guias</h3>
                <div class="mt-4 grid gap-3">
                    @forelse ($chartStatusDistribution['segments'] as $seg)
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $seg['color'] }}"></span>
                                    <p class="truncate text-sm font-bold text-gray-700">{{ $seg['label'] }}</p>
                                </div>
                                <span class="text-sm font-black text-gray-950">{{ $seg['count'] }}</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" style="width: {{ round(($seg['count'] / $statusTotal) * 100) }}%; background: {{ $seg['color'] }}"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm font-semibold text-gray-500">Todavia no hay guias en este periodo.</p>
                    @endforelse
                </div>
            </div>
        </section>
        </details>

        <details class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Mas informacion</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">Opcional</span>
            </summary>
        <section class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
            @if ($productSuggestions['show'])
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-blue-700">Atajo inteligente</p>
                            <h3 class="mt-1 text-base font-black text-blue-950">Productos rapidos</h3>
                        </div>
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-black text-blue-700">{{ $productSuggestions['repeated_count'] }}</span>
                    </div>
                    @if (! empty($productSuggestions['items']))
                        <p class="mt-2 text-sm font-semibold text-blue-800">Estos productos aparecen varias veces y pueden guardarse para crear guias mas rapido.</p>
                        <div class="mt-4 grid gap-2">
                            @foreach ($productSuggestions['items'] as $suggestion)
                                <a href="{{ $suggestion['route'] }}" class="rounded-lg border border-blue-100 bg-white p-3 hover:border-blue-300">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate text-sm font-black text-gray-950" title="{{ $suggestion['name'] }}">{{ $suggestion['name'] }}</p>
                                        <span class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-black text-blue-800">{{ $suggestion['count'] }} veces</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-600">Guardar como producto rapido</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm font-semibold text-blue-800">Tus productos repetidos ya estan guardados. Puedes reutilizarlos al crear una guia y evitar escribirlos de nuevo.</p>
                        <div class="mt-4 rounded-lg border border-blue-100 bg-white p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xl font-black text-blue-950">{{ $productSuggestions['ready_count'] }}</p>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Listos para reutilizar</p>
                                </div>
                                <a href="{{ route('quick-products.index') }}" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-black text-white hover:bg-blue-800">Ver productos</a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if (! empty($chartTopProducts))
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                    <div class="mt-4 grid gap-3">
                        @foreach ($chartTopProducts as $p)
                            <div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="truncate text-sm font-black text-gray-950" title="{{ $p['name'] }}">{{ $p['name'] }}</p>
                                    <span class="shrink-0 text-xs font-black text-gray-500">{{ $p['count'] }}</span>
                                </div>
                                <div class="mt-1.5 h-2.5 rounded-full bg-gray-100">
                                    <div class="h-2.5 rounded-full bg-blue-700" style="width: {{ $p['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                @php $mt = $chartMonthlyTrend; @endphp
                <div class="mt-4 flex h-28 items-end justify-around gap-3">
                    @foreach ($mt['months'] as $m)
                        @php $h = max(8, round(($m['count'] / $mt['max']) * 82)); @endphp
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-black text-gray-950">{{ $m['count'] }}</span>
                            <div class="w-full rounded-t-md bg-gray-900" style="height:{{ $h }}px; min-height:6px;"></div>
                            <span class="text-xs font-bold text-gray-500">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Actividad reciente</h3>
                <div class="mt-3 divide-y divide-gray-100">
                    @forelse ($recentAudit as $audit)
                        <div class="flex items-center justify-between gap-3 py-2">
                            <p class="truncate text-xs font-semibold text-gray-700">{{ \Illuminate\Support\Str::limit($audit['description'], 44) }}</p>
                            <span class="shrink-0 text-xs font-bold text-gray-400">{{ \Carbon\Carbon::parse($audit['date'])->diffForHumans(null, true) }}</span>
                        </div>
                    @empty
                        <p class="py-3 text-sm font-semibold text-gray-500">Sin actividad reciente.</p>
                    @endforelse
                </div>
            </div>
        </section>
        </details>

        @if (! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out']))
            <details class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Alertas de inventario</span>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-800">{{ count($inventoryAlerts['low']) + count($inventoryAlerts['out']) }}</span>
                </summary>
            <section class="mt-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Alertas de inventario</h3>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach ($inventoryAlerts['out'] as $p)
                        <a href="{{ route('inventory.index', ['stock' => 'out']) }}" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-black text-red-800 hover:bg-red-100">
                            Agotado: {{ $p->name }}
                        </a>
                    @endforeach
                    @foreach ($inventoryAlerts['low'] as $p)
                        <a href="{{ route('inventory.index', ['stock' => 'low']) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-800 hover:bg-amber-100">
                            Stock bajo: {{ $p->name }} ({{ $p->stock }}/{{ $p->stock_minimum }})
                        </a>
                    @endforeach
                </div>
            </section>
            </details>
        @endif
            </div>
        </details>
    </div>

    <script>
        const rangeSelect = document.getElementById('dash-range');
        const datesDiv = document.getElementById('dash-dates');

        if (rangeSelect && datesDiv) {
            function toggleDates() {
                if (rangeSelect.value === 'custom') {
                    datesDiv.classList.remove('hidden');
                    datesDiv.classList.add('flex');
                } else {
                    datesDiv.classList.add('hidden');
                    datesDiv.classList.remove('flex');
                }
            }

            rangeSelect.addEventListener('change', toggleDates);
            toggleDates();
        }

        const copyDashboardReport = document.getElementById('copy-dashboard-report');

        if (copyDashboardReport) {
            copyDashboardReport.addEventListener('click', async () => {
                const status = document.getElementById('dashboard-report-copy-status');
                const text = copyDashboardReport.dataset.report || '';

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
