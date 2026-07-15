<?php
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
?>

<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard','description' => 'Tu centro de control para vender, preparar y entregar mejor.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','description' => 'Tu centro de control para vender, preparar y entregar mejor.']); ?>
             <?php $__env->slot('eyebrow', null, []); ?> <?php echo e(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY')); ?> <?php $__env->endSlot(); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <select name="range" id="dash-range" class="appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-8 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <option value="today" <?php echo e($dateRange['range'] === 'today' ? 'selected' : ''); ?>>Hoy</option>
                            <option value="7d" <?php echo e($dateRange['range'] === '7d' ? 'selected' : ''); ?>>Ultimos 7 dias</option>
                            <option value="30d" <?php echo e($dateRange['range'] === '30d' ? 'selected' : ''); ?>>Ultimos 30 dias</option>
                            <option value="90d" <?php echo e($dateRange['range'] === '90d' ? 'selected' : ''); ?>>Ultimos 90 dias</option>
                            <option value="custom" <?php echo e(!in_array($dateRange['range'], ['today','7d','30d','90d']) ? 'selected' : ''); ?>>Personalizado</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 4 4-4" /></svg>
                    </div>
                    <div id="dash-dates" class="hidden items-center gap-2">
                        <input type="date" name="from" value="<?php echo e($dateRange['from']); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <span class="text-sm text-gray-500">a</span>
                        <input type="date" name="to" value="<?php echo e($dateRange['to']); ?>" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Aplicar</button>
                </form>
                <?php if(Auth::user()->canCreateShipments()): ?>
                    <a href="<?php echo e(route('shipments.create')); ?>" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                <?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="p-4 sm:p-6 lg:p-8">
        <?php if($onboarding['show']): ?>
            <section class="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Primeros pasos</p>
                        <h2 class="mt-1 text-base font-black text-gray-950">Completa la base profesional de tu negocio</h2>
                    </div>
                    <div class="min-w-40">
                        <div class="h-2 rounded-full bg-white">
                            <div class="h-2 rounded-full bg-blue-700" style="width: <?php echo e(round(($onboarding['completed'] / max(1, $onboarding['total'])) * 100)); ?>%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs font-bold text-blue-800"><?php echo e($onboarding['completed']); ?>/<?php echo e($onboarding['total']); ?> completo</p>
                    </div>
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <?php $__currentLoopData = $onboarding['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($step['route']); ?>" class="rounded-lg border <?php echo e($step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white'); ?> p-3 hover:border-blue-300">
                            <p class="text-sm font-black text-gray-950"><?php echo e($step['done'] ? 'Listo' : $loop->iteration.'.'); ?> <?php echo e($step['label']); ?></p>
                            <p class="mt-1 text-xs font-semibold text-gray-600"><?php echo e($step['description']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if($trialGuideCounter): ?>
            <section class="mb-5 rounded-lg border border-blue-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Prueba gratis</p>
                        <h2 class="mt-1 text-base font-black text-gray-950">Te quedan <?php echo e($trialGuideCounter['remaining']); ?> de <?php echo e($trialGuideCounter['total']); ?> guias</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-3 w-48 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full rounded-full bg-blue-700" style="width:<?php echo e(($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / max(1, $trialGuideCounter['total']) * 100); ?>%"></div>
                        </div>
                        <span class="text-sm font-black text-blue-800"><?php echo e($trialGuideCounter['remaining']); ?>/<?php echo e($trialGuideCounter['total']); ?></span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($priorityTone['badge']); ?>"><?php echo e($todayPriority['label']); ?></span>
                    <h2 class="mt-3 text-lg font-black text-gray-950">Empieza por esto: <?php echo e($todayPriority['title']); ?></h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600"><?php echo e($todayPriority['description']); ?></p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e($todayPriority['route']); ?>" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm <?php echo e($priorityTone['button']); ?>"><?php echo e($todayPriority['action']); ?></a>
                    <a href="<?php echo e(route('daily-tasks.index')); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Ver tareas</a>
                </div>
            </div>

            <div class="mt-4 grid gap-3 lg:grid-cols-3">
                <?php $__currentLoopData = $todayPriority['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-500">Paso <?php echo e($loop->iteration); ?></p>
                        <p class="mt-1 text-sm font-bold text-gray-800"><?php echo e($step); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <a href="<?php echo e(route('daily-tasks.index')); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                    <p class="mt-1 text-2xl font-black text-gray-950"><?php echo e($pendingTotal); ?></p>
                </a>
                <a href="<?php echo e(route('shipments.index', ['status' => 'failed_delivery'])); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-red-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                    <p class="mt-1 text-2xl font-black <?php echo e($metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>"><?php echo e($metrics['issues']); ?></p>
                </a>
                <a href="<?php echo e(route('shipments.index', ['status' => 'created'])); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Por imprimir</p>
                    <p class="mt-1 text-2xl font-black text-gray-950"><?php echo e($metrics['pending_print']); ?></p>
                </a>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregadas</p>
                    <p class="mt-1 text-2xl font-black text-emerald-700"><?php echo e($metrics['delivered_today']); ?></p>
                </div>
            </div>
        </section>

        <details class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Ver mas detalles solo si los necesitas</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700">Opcional</span>
            </summary>
            <div class="mt-5">

        <?php if($professionalScore['show']): ?>
            <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Base profesional de tu marca</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700"><?php echo e($professionalScore['score']); ?>%</span>
                </summary>
            <section class="mt-4">
                <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)] xl:items-center">
                    <div class="flex justify-center">
                        <div class="relative grid h-40 w-40 place-items-center rounded-full" style="background: conic-gradient(<?php echo e($professionalTone['ring']); ?> <?php echo e($professionalScore['score']); ?>%, #e5e7eb 0);">
                            <div class="grid h-28 w-28 place-items-center rounded-full bg-white shadow-inner">
                                <div class="text-center">
                                    <p class="text-3xl font-black text-gray-950"><?php echo e($professionalScore['score']); ?>%</p>
                                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">marca</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($professionalTone['panel']); ?> <?php echo e($professionalTone['text']); ?>"><?php echo e($professionalScore['label']); ?></span>
                                <h2 class="mt-3 text-xl font-black text-gray-950">Score de Profesionalismo</h2>
                                <p class="mt-1 max-w-2xl text-sm font-semibold text-gray-600">
                                    Completa estos puntos para que tu negocio se vea mas confiable en etiquetas, seguimiento y comunicacion con clientes.
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-black text-gray-950"><?php echo e($professionalScore['completed']); ?>/<?php echo e($professionalScore['total']); ?> listo</p>
                                <p class="text-xs font-semibold text-gray-500">Base comercial</p>
                            </div>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full <?php echo e($professionalTone['bar']); ?>" style="width: <?php echo e($professionalScore['score']); ?>%"></div>
                        </div>
                        <div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                            <?php $__currentLoopData = collect($professionalScore['steps'])->where('done', false)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($step['route']); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:border-blue-300 hover:bg-white">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-black text-gray-950"><?php echo e($step['label']); ?></p>
                                        <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-blue-700"><?php echo e($step['action']); ?></span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-600"><?php echo e($step['description']); ?></p>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(collect($professionalScore['steps'])->where('done', false)->isEmpty()): ?>
                                <a href="<?php echo e(route('shipments.index')); ?>" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 hover:bg-white">
                                    <p class="text-sm font-black text-emerald-900">Todo listo</p>
                                    <p class="mt-1 text-xs font-semibold text-emerald-800">Tu marca ya tiene una base profesional completa.</p>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            </details>
        <?php endif; ?>

        <details class="mb-5 rounded-lg border p-4 shadow-sm <?php echo e($workdayTone['panel']); ?>">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Salud operativa</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm"><?php echo e($workdaySummary['progress']); ?>%</span>
            </summary>
        <section class="mt-4">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-center">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($workdayTone['badge']); ?>"><?php echo e($workdaySummary['label']); ?></span>
                    <h2 class="mt-3 text-xl font-black text-gray-950"><?php echo e($workdaySummary['title']); ?></h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold <?php echo e($workdayTone['text']); ?>"><?php echo e($workdaySummary['description']); ?></p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                            <p class="mt-1 text-2xl font-black text-gray-950"><?php echo e($pendingTotal); ?></p>
                        </div>
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                            <p class="mt-1 text-2xl font-black <?php echo e($metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>"><?php echo e($metrics['issues']); ?></p>
                        </div>
                        <div class="rounded-lg border border-white/70 bg-white/80 p-3">
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Quietas</p>
                            <p class="mt-1 text-2xl font-black <?php echo e($operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950'); ?>"><?php echo e($operationHealth['stale']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-gray-500">Avance operativo</p>
                            <p class="mt-1 text-3xl font-black text-gray-950"><?php echo e($workdaySummary['progress']); ?>%</p>
                        </div>
                        <a href="<?php echo e(route('daily-tasks.index')); ?>" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm <?php echo e($workdayTone['button']); ?>">Abrir Tareas Diarias</a>
                    </div>
                    <div class="mt-4 h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full <?php echo e($workdayTone['bar']); ?>" style="width: <?php echo e($workdaySummary['progress']); ?>%"></div>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-gray-500">Este indicador baja cuando hay novedades, guias sin imprimir o guias quietas.</p>
                </div>
            </div>
        </section>
        </details>

        <section class="mb-4 rounded-lg border p-4 shadow-sm <?php echo e($priorityTone['panel']); ?>">
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_240px] xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($priorityTone['badge']); ?>"><?php echo e($todayPriority['label']); ?></span>
                        <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm"><?php echo e($todayPriority['metric']); ?> <?php echo e($todayPriority['metricLabel']); ?></span>
                    </div>
                    <h2 class="mt-3 text-lg font-black text-gray-950"><?php echo e($todayPriority['title']); ?></h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold <?php echo e($priorityTone['text']); ?>"><?php echo e($todayPriority['description']); ?></p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="<?php echo e($todayPriority['route']); ?>" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-black shadow-sm <?php echo e($priorityTone['button']); ?>"><?php echo e($todayPriority['action']); ?></a>
                        <a href="<?php echo e(route('daily-tasks.index')); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Abrir Tareas Diarias</a>
                        <button type="button" id="copy-dashboard-report" data-report="<?php echo e($dashboardReportText); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Copiar reporte</button>
                        <span id="dashboard-report-copy-status" class="min-h-5 self-center text-xs font-bold text-emerald-700" aria-live="polite"></span>
                    </div>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Plan de accion</p>
                    <div class="mt-3 space-y-2">
                        <?php $__currentLoopData = $todayPriority['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full <?php echo e($priorityTone['dot']); ?>"></span>
                                <p class="text-sm font-bold text-gray-800"><?php echo e($step); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </section>

        <details class="mb-5 rounded-lg border p-4 shadow-sm <?php echo e($moneyTone['panel']); ?>">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Resumen de dinero</span>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-gray-700 shadow-sm">$<?php echo e(number_format($moneySummary['moneyToWatch'], 0, ',', '.')); ?> a vigilar</span>
            </summary>
        <section class="mt-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($moneyTone['badge']); ?>"><?php echo e($moneySummary['label']); ?></span>
                    <h2 class="mt-3 text-xl font-black text-gray-950">Resumen de dinero</h2>
                    <p class="mt-1 max-w-3xl text-sm font-semibold <?php echo e($moneyTone['text']); ?>">
                        Una vista simple para saber que ya se entrego, que esta pendiente de recaudo y que valor merece seguimiento.
                    </p>
                </div>
                <a href="<?php echo e(route('shipments.index')); ?>" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm hover:bg-gray-50">Revisar guias</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Creado en <?php echo e($rangeLabel); ?></p>
                    <p class="mt-2 text-2xl font-black text-gray-950">$<?php echo e(number_format($moneySummary['createdValue'], 0, ',', '.')); ?></p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Valor total de guias no canceladas.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregado</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700">$<?php echo e(number_format($moneySummary['deliveredValue'], 0, ',', '.')); ?></p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Ingresos asociados a guias entregadas.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Recaudo pendiente</p>
                    <p class="mt-2 text-2xl font-black <?php echo e($moneySummary['collectionOpen'] > 0 ? 'text-amber-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['collectionOpen'], 0, ',', '.')); ?></p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Contraentrega abierto sin cierre final.</p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-4">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Dinero a vigilar</p>
                    <p class="mt-2 text-2xl font-black <?php echo e($moneySummary['moneyToWatch'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['moneyToWatch'], 0, ',', '.')); ?></p>
                    <p class="mt-1 text-xs font-semibold text-gray-500">Novedades, liquidaciones o recaudos pendientes.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <a href="<?php echo e(route('shipments.index', ['status' => 'failed_delivery'])); ?>" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">En novedades</p>
                    <p class="mt-1 text-lg font-black <?php echo e($moneySummary['issueValue'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['issueValue'], 0, ',', '.')); ?></p>
                </a>
                <a href="<?php echo e(route('shipments.index')); ?>" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Por liquidar</p>
                    <p class="mt-1 text-lg font-black <?php echo e($moneySummary['pendingSettlementValue'] > 0 ? 'text-blue-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['pendingSettlementValue'], 0, ',', '.')); ?></p>
                </a>
                <a href="<?php echo e(route('shipments.index', ['status' => 'cancelled'])); ?>" class="rounded-lg border border-white/70 bg-white/80 p-3 hover:bg-white">
                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">Cancelado en <?php echo e($rangeLabel); ?></p>
                    <p class="mt-1 text-lg font-black text-gray-950">$<?php echo e(number_format($moneySummary['cancelledValue'], 0, ',', '.')); ?></p>
                </a>
            </div>
        </section>
        </details>

        <?php if(! empty($growthActions)): ?>
            <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Acciones para vender y atender mejor</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-800"><?php echo e(count($growthActions)); ?></span>
                </summary>
            <section class="mt-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-blue-700">Siguiente movimiento</p>
                        <h2 class="mt-1 text-xl font-black text-gray-950">Acciones para vender y atender mejor</h2>
                        <p class="mt-1 max-w-3xl text-sm font-semibold text-gray-600">Recomendaciones comerciales basadas en tu marca, tus productos y el estado actual de tus guias.</p>
                    </div>
                    <?php if(Auth::user()->canCreateShipments()): ?>
                        <a href="<?php echo e(route('shipments.create')); ?>" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-blue-800">Crear guia</a>
                    <?php endif; ?>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <?php $__currentLoopData = $growthActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $growthTone = [
                                'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'text' => 'text-emerald-800', 'badge' => 'bg-emerald-100 text-emerald-800', 'button' => 'text-emerald-800'],
                                'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'text' => 'text-amber-800', 'badge' => 'bg-amber-100 text-amber-800', 'button' => 'text-amber-800'],
                                'red' => ['panel' => 'border-red-200 bg-red-50', 'text' => 'text-red-800', 'badge' => 'bg-red-100 text-red-800', 'button' => 'text-red-800'],
                                'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-800', 'button' => 'text-blue-800'],
                            ][$action['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-800', 'button' => 'text-blue-800'];
                        ?>
                        <a href="<?php echo e($action['route']); ?>" class="rounded-lg border p-4 hover:bg-white <?php echo e($growthTone['panel']); ?>">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-gray-950"><?php echo e($action['label']); ?></p>
                                    <p class="mt-1 text-xs font-semibold <?php echo e($growthTone['text']); ?>"><?php echo e($action['description']); ?></p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-black <?php echo e($growthTone['badge']); ?>"><?php echo e($action['metric']); ?></span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="text-[11px] font-black uppercase tracking-wider text-gray-500"><?php echo e($action['metric_label']); ?></p>
                                <span class="text-xs font-black <?php echo e($growthTone['button']); ?>"><?php echo e($action['action']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            </details>
        <?php endif; ?>

        <details class="mb-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Detalle de salud y acciones</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700"><?php echo e($operationHealth['score']); ?>/100</span>
            </summary>
        <section class="mt-4 grid gap-4 xl:grid-cols-[1.35fr_0.9fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="grid gap-5 lg:grid-cols-[220px_1fr] lg:items-center">
                    <div class="flex justify-center">
                        <div class="relative grid h-44 w-44 place-items-center rounded-full" style="background: conic-gradient(<?php echo e($healthTone['ring']); ?> <?php echo e($operationHealth['score']); ?>%, #e5e7eb 0);">
                            <div class="grid h-32 w-32 place-items-center rounded-full bg-white shadow-inner">
                                <div class="text-center">
                                    <p class="text-4xl font-black text-gray-950"><?php echo e($operationHealth['score']); ?></p>
                                    <p class="text-xs font-black uppercase tracking-wider text-gray-500">salud</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wider <?php echo e($healthTone['soft']); ?> <?php echo e($healthTone['text']); ?>"><?php echo e($operationHealth['label']); ?></span>
                        <h2 class="mt-3 text-2xl font-black text-gray-950">Hoy tu operacion esta bajo control</h2>
                        <p class="mt-2 max-w-2xl text-sm font-semibold text-gray-600">
                            <?php echo e($rangeLabel); ?>: <?php echo e($metrics['shipments_today']); ?> guia(s), <?php echo e($metrics['delivered_today']); ?> entregada(s), <?php echo e($pendingTotal); ?> pendiente(s) operativo(s).
                        </p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <a href="<?php echo e(route('daily-tasks.index')); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-blue-50">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Pendientes</p>
                                <p class="mt-1 text-2xl font-black text-gray-950"><?php echo e($pendingTotal); ?></p>
                            </a>
                            <a href="<?php echo e(route('shipments.index', ['status' => 'failed_delivery'])); ?>" class="rounded-lg border border-gray-200 bg-gray-50 p-3 hover:bg-red-50">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Novedades</p>
                                <p class="mt-1 text-2xl font-black <?php echo e($metrics['issues'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>"><?php echo e($metrics['issues']); ?></p>
                            </a>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos</p>
                                <p class="mt-1 text-2xl font-black text-emerald-700">$<?php echo e(number_format($moneyTotal, 0, ',', '.')); ?></p>
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
                    <a href="<?php echo e(route('daily-tasks.index')); ?>" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver tareas</a>
                </div>
                <div class="mt-4 grid gap-3">
                    <?php $__currentLoopData = array_slice($smartActions, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $actionTone = [
                                'red' => 'border-red-200 bg-red-50 text-red-800',
                                'blue' => 'border-blue-200 bg-blue-50 text-blue-800',
                                'amber' => 'border-amber-200 bg-amber-50 text-amber-800',
                                'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                'slate' => 'border-gray-200 bg-gray-50 text-gray-800',
                            ][$action['tone']] ?? 'border-gray-200 bg-gray-50 text-gray-800';
                        ?>
                        <a href="<?php echo e($action['route']); ?>" class="rounded-lg border p-3 hover:bg-white <?php echo e($actionTone); ?>">
                            <p class="text-sm font-black"><?php echo e($action['label']); ?></p>
                            <p class="mt-1 text-xs font-semibold opacity-80"><?php echo e($action['description']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </aside>
        </section>
        </details>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregas</p>
                <div class="mt-3 flex items-center gap-3">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full" style="background: conic-gradient(<?php echo e($deliveryTone); ?> <?php echo e($deliveryRingValue); ?>%, #e5e7eb 0);">
                        <div class="grid h-11 w-11 place-items-center rounded-full bg-white">
                            <span class="text-center text-sm font-black text-gray-950"><?php echo e($deliveryRate['total'] === 0 ? 'Sin datos' : $deliveryRate['rate'].'%'); ?></span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-950"><?php echo e($deliveryRate['delivered']); ?> de <?php echo e($deliveryRate['total']); ?></p>
                        <p class="mt-1 text-xs font-semibold text-gray-500">Guias entregadas en <?php echo e($rangeLabel); ?></p>
                    </div>
                </div>
            </div>

            <a href="<?php echo e(route('shipments.index', ['status' => 'created'])); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Preparacion</p>
                <p class="mt-2 text-2xl font-black text-gray-950"><?php echo e($metrics['pending_print']); ?></p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-700" style="width: <?php echo e(min(100, $metrics['pending_print'] * 12)); ?>%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Guias por imprimir</p>
            </a>

            <a href="<?php echo e(route('shipments.index', ['status' => 'on_route'])); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">En movimiento</p>
                <p class="mt-2 text-2xl font-black text-gray-950"><?php echo e($metrics['in_transit']); ?></p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-emerald-600" style="width: <?php echo e(min(100, $metrics['in_transit'] * 10)); ?>%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Guias en ruta o bodega</p>
            </a>

            <a href="<?php echo e(route('daily-tasks.index')); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Sin movimiento</p>
                <p class="mt-2 text-2xl font-black <?php echo e($operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950'); ?>"><?php echo e($operationHealth['stale']); ?></p>
                <div class="mt-3 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-amber-500" style="width: <?php echo e(min(100, $operationHealth['stale'] * 18)); ?>%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-gray-500">Mas de 24 horas quietas</p>
            </a>
        </section>

        <?php if(! empty($alerts)): ?>
            <div class="mt-5 flex flex-wrap gap-3">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($alert['route']); ?>" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-black shadow-sm hover:bg-white <?php echo e($alert['bg']); ?>">
                        <svg class="h-4 w-4 <?php echo e($alert['color']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($alert['icon']); ?>" />
                        </svg>
                        <span><?php echo e($alert['count']); ?> <?php echo e($alert['label']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <details class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                <span>Graficas y analisis</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-black text-gray-700"><?php echo e($rangeLabel); ?></span>
            </summary>
        <section class="mt-4 grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</h3>
                    <span class="shrink-0 text-xs font-bold text-blue-700"><?php echo e($rangeLabel); ?></span>
                </div>
                <?php $sd = $chartShipmentsByDay; ?>
                <div class="mt-4 overflow-x-auto pb-2">
                    <div class="flex h-36 min-w-max items-end gap-2">
                        <?php $__currentLoopData = $sd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $h = max(8, round(($d['count'] / $sd['max']) * 105)); ?>
                            <div class="flex h-full w-7 shrink-0 flex-col items-center justify-end gap-1">
                                <span class="text-xs font-black text-gray-950"><?php echo e($d['count']); ?></span>
                                <div class="w-full rounded-t-md bg-blue-700" style="height: <?php echo e($h); ?>px; min-height: 6px;"></div>
                                <span class="text-xs font-bold text-gray-500"><?php echo e($d['label']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos por entregas</h3>
                    <span class="shrink-0 text-xs font-bold text-emerald-700"><?php echo e($rangeLabel); ?></span>
                </div>
                <?php $rd = $chartRevenueByDay; ?>
                <div class="mt-4 overflow-x-auto pb-2">
                    <div class="flex h-36 min-w-max items-end gap-2">
                        <?php $__currentLoopData = $rd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $h = max(8, round(($d['revenue'] / $rd['max']) * 105)); ?>
                            <div class="flex h-full w-8 shrink-0 flex-col items-center justify-end gap-1">
                                <span class="max-w-full truncate text-[11px] font-black text-emerald-700">$<?php echo e(number_format($d['revenue'], 0, ',', '.')); ?></span>
                                <div class="w-full rounded-t-md bg-emerald-600" style="height: <?php echo e($h); ?>px; min-height: 6px;"></div>
                                <span class="text-xs font-bold text-gray-500"><?php echo e($d['label']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="min-w-0 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Estado de guias</h3>
                <div class="mt-4 grid gap-3">
                    <?php $__empty_1 = true; $__currentLoopData = $chartStatusDistribution['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: <?php echo e($seg['color']); ?>"></span>
                                    <p class="truncate text-sm font-bold text-gray-700"><?php echo e($seg['label']); ?></p>
                                </div>
                                <span class="text-sm font-black text-gray-950"><?php echo e($seg['count']); ?></span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full" style="width: <?php echo e(round(($seg['count'] / $statusTotal) * 100)); ?>%; background: <?php echo e($seg['color']); ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm font-semibold text-gray-500">Todavia no hay guias en este periodo.</p>
                    <?php endif; ?>
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
            <?php if($productSuggestions['show']): ?>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-blue-700">Atajo inteligente</p>
                            <h3 class="mt-1 text-base font-black text-blue-950">Productos rapidos</h3>
                        </div>
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-black text-blue-700"><?php echo e($productSuggestions['repeated_count']); ?></span>
                    </div>
                    <?php if(! empty($productSuggestions['items'])): ?>
                        <p class="mt-2 text-sm font-semibold text-blue-800">Estos productos aparecen varias veces y pueden guardarse para crear guias mas rapido.</p>
                        <div class="mt-4 grid gap-2">
                            <?php $__currentLoopData = $productSuggestions['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($suggestion['route']); ?>" class="rounded-lg border border-blue-100 bg-white p-3 hover:border-blue-300">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate text-sm font-black text-gray-950" title="<?php echo e($suggestion['name']); ?>"><?php echo e($suggestion['name']); ?></p>
                                        <span class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-black text-blue-800"><?php echo e($suggestion['count']); ?> veces</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-gray-600">Guardar como producto rapido</p>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-2 text-sm font-semibold text-blue-800">Tus productos repetidos ya estan guardados. Puedes reutilizarlos al crear una guia y evitar escribirlos de nuevo.</p>
                        <div class="mt-4 rounded-lg border border-blue-100 bg-white p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xl font-black text-blue-950"><?php echo e($productSuggestions['ready_count']); ?></p>
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Listos para reutilizar</p>
                                </div>
                                <a href="<?php echo e(route('quick-products.index')); ?>" class="rounded-lg bg-blue-700 px-3 py-2 text-xs font-black text-white hover:bg-blue-800">Ver productos</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if(! empty($chartTopProducts)): ?>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                    <div class="mt-4 grid gap-3">
                        <?php $__currentLoopData = $chartTopProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="truncate text-sm font-black text-gray-950" title="<?php echo e($p['name']); ?>"><?php echo e($p['name']); ?></p>
                                    <span class="shrink-0 text-xs font-black text-gray-500"><?php echo e($p['count']); ?></span>
                                </div>
                                <div class="mt-1.5 h-2.5 rounded-full bg-gray-100">
                                    <div class="h-2.5 rounded-full bg-blue-700" style="width: <?php echo e($p['pct']); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                <?php $mt = $chartMonthlyTrend; ?>
                <div class="mt-4 flex h-28 items-end justify-around gap-3">
                    <?php $__currentLoopData = $mt['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $h = max(8, round(($m['count'] / $mt['max']) * 82)); ?>
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-black text-gray-950"><?php echo e($m['count']); ?></span>
                            <div class="w-full rounded-t-md bg-gray-900" style="height:<?php echo e($h); ?>px; min-height:6px;"></div>
                            <span class="text-xs font-bold text-gray-500"><?php echo e($m['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Actividad reciente</h3>
                <div class="mt-3 divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentAudit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between gap-3 py-2">
                            <p class="truncate text-xs font-semibold text-gray-700"><?php echo e(\Illuminate\Support\Str::limit($audit['description'], 44)); ?></p>
                            <span class="shrink-0 text-xs font-bold text-gray-400"><?php echo e(\Carbon\Carbon::parse($audit['date'])->diffForHumans(null, true)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-3 text-sm font-semibold text-gray-500">Sin actividad reciente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        </details>

        <?php if(! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out'])): ?>
            <details class="mt-5 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-black text-gray-950">
                    <span>Alertas de inventario</span>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-800"><?php echo e(count($inventoryAlerts['low']) + count($inventoryAlerts['out'])); ?></span>
                </summary>
            <section class="mt-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Alertas de inventario</h3>
                <div class="mt-3 flex flex-wrap gap-3">
                    <?php $__currentLoopData = $inventoryAlerts['out']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('inventory.index', ['stock' => 'out'])); ?>" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-black text-red-800 hover:bg-red-100">
                            Agotado: <?php echo e($p->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $inventoryAlerts['low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('inventory.index', ['stock' => 'low'])); ?>" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-800 hover:bg-amber-100">
                            Stock bajo: <?php echo e($p->name); ?> (<?php echo e($p->stock); ?>/<?php echo e($p->stock_minimum); ?>)
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
            </details>
        <?php endif; ?>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/dashboard.blade.php ENDPATH**/ ?>