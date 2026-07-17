<?php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $deliveryTone = $deliveryRate['total'] === 0 ? '#9ca3af' : ($deliveryRate['rate'] >= 80 ? '#059669' : ($deliveryRate['rate'] >= 50 ? '#d97706' : '#dc2626'));
    $deliveryRingValue = $deliveryRate['total'] === 0 ? 100 : $deliveryRate['rate'];
    $statusTotal = max(1, $chartStatusDistribution['total'] ?? 1);
    $moneyTone = [
        'emerald' => ['panel' => 'border-emerald-200 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'text' => 'text-emerald-800'],
        'amber' => ['panel' => 'border-amber-200 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'text' => 'text-amber-800'],
        'blue' => ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800'],
    ][$moneySummary['tone']] ?? ['panel' => 'border-blue-200 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'text' => 'text-blue-800'];
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard','description' => 'Los resultados de tu negocio, de un vistazo.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','description' => 'Los resultados de tu negocio, de un vistazo.']); ?>
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

    <div class="flex h-full flex-col p-3 sm:p-4 lg:p-4">
        <?php if($operationHealth['stale'] > 0): ?>
            <section class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-amber-700">Recordatorio diario</p>
                        <h2 class="text-sm font-black text-gray-950">
                            Tienes <?php echo e($operationHealth['stale']); ?> guia<?php echo e($operationHealth['stale'] === 1 ? '' : 's'); ?> sin actualizar en mas de 24 horas
                        </h2>
                    </div>
                    <a href="<?php echo e(route('daily-tasks.index')); ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-black text-white shadow-sm hover:bg-amber-700">
                        Actualizar guias
                    </a>
                </div>
            </section>
        <?php endif; ?>

        <?php if($onboarding['show']): ?>
            <section class="mb-3 rounded-lg border border-blue-200 bg-blue-50 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-blue-700">Primeros pasos</p>
                        <h2 class="text-sm font-black text-gray-950">Completa la base profesional de tu negocio</h2>
                    </div>
                    <div class="min-w-40">
                        <div class="h-1.5 rounded-full bg-white">
                            <div class="h-1.5 rounded-full bg-blue-700" style="width: <?php echo e(round(($onboarding['completed'] / max(1, $onboarding['total'])) * 100)); ?>%"></div>
                        </div>
                        <p class="mt-0.5 text-right text-[11px] font-bold text-blue-800"><?php echo e($onboarding['completed']); ?>/<?php echo e($onboarding['total']); ?> completo</p>
                    </div>
                </div>
                <div class="mt-2 grid gap-2 md:grid-cols-3">
                    <?php $__currentLoopData = $onboarding['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($step['route']); ?>" class="rounded-lg border <?php echo e($step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white'); ?> p-2 hover:border-blue-300">
                            <p class="text-xs font-black text-gray-950"><?php echo e($step['done'] ? 'Listo' : $loop->iteration.'.'); ?> <?php echo e($step['label']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if($trialGuideCounter): ?>
            <section class="mb-3 rounded-lg border border-blue-200 bg-white p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-blue-700">Prueba gratis</p>
                        <h2 class="text-sm font-black text-gray-950">Te quedan <?php echo e($trialGuideCounter['remaining']); ?> de <?php echo e($trialGuideCounter['total']); ?> guias</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-36 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full rounded-full bg-blue-700" style="width:<?php echo e(($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / max(1, $trialGuideCounter['total']) * 100); ?>%"></div>
                        </div>
                        <span class="text-xs font-black text-blue-800"><?php echo e($trialGuideCounter['remaining']); ?>/<?php echo e($trialGuideCounter['total']); ?></span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950"><?php echo e($metrics['shipments_today']); ?></p>
                <p class="mt-1.5 text-xs font-semibold text-gray-500"><?php echo e($rangeLabel); ?></p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Entregas</p>
                <div class="mt-2 flex items-center gap-3">
                    <?php if (isset($component)) { $__componentOriginal3418f766138f93001515fbf6377748f7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3418f766138f93001515fbf6377748f7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ring-gauge','data' => ['score' => $deliveryRingValue,'size' => 68,'stroke' => 8,'color' => $deliveryTone,'class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ring-gauge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['score' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deliveryRingValue),'size' => 68,'stroke' => 8,'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deliveryTone),'class' => 'shrink-0']); ?>
                        <div class="grid h-12 w-12 place-items-center rounded-full bg-white">
                            <span class="text-center text-xs font-black text-gray-950"><?php echo e($deliveryRate['total'] === 0 ? '-' : $deliveryRate['rate'].'%'); ?></span>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3418f766138f93001515fbf6377748f7)): ?>
<?php $attributes = $__attributesOriginal3418f766138f93001515fbf6377748f7; ?>
<?php unset($__attributesOriginal3418f766138f93001515fbf6377748f7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3418f766138f93001515fbf6377748f7)): ?>
<?php $component = $__componentOriginal3418f766138f93001515fbf6377748f7; ?>
<?php unset($__componentOriginal3418f766138f93001515fbf6377748f7); ?>
<?php endif; ?>
                    <div>
                        <p class="text-sm font-bold text-gray-950"><?php echo e($deliveryRate['delivered']); ?> de <?php echo e($deliveryRate['total']); ?></p>
                        <p class="text-xs font-semibold text-gray-500">Entregadas en <?php echo e($rangeLabel); ?></p>
                    </div>
                </div>
            </div>

            <a href="<?php echo e(route('shipments.index', ['status' => 'created'])); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Preparacion</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950"><?php echo e($metrics['pending_print']); ?></p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-blue-700" style="width: <?php echo e(min(100, $metrics['pending_print'] * 12)); ?>%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">Guias por imprimir</p>
            </a>

            <a href="<?php echo e(route('shipments.index', ['status' => 'on_route'])); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">En movimiento</p>
                <p class="mt-1.5 text-3xl font-black text-gray-950"><?php echo e($metrics['in_transit']); ?></p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-emerald-600" style="width: <?php echo e(min(100, $metrics['in_transit'] * 10)); ?>%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">En ruta o bodega</p>
            </a>

            <a href="<?php echo e(route('daily-tasks.index')); ?>" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase tracking-wider text-gray-500">Sin movimiento</p>
                <p class="mt-1.5 text-3xl font-black <?php echo e($operationHealth['stale'] > 0 ? 'text-amber-700' : 'text-gray-950'); ?>"><?php echo e($operationHealth['stale']); ?></p>
                <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-amber-500" style="width: <?php echo e(min(100, $operationHealth['stale'] * 18)); ?>%"></div>
                </div>
                <p class="mt-1.5 text-xs font-semibold text-gray-500">Mas de 24h quietas</p>
            </a>
        </section>

        <?php if(! empty($alerts) || ! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out'])): ?>
            <div class="mt-3 flex flex-wrap gap-2">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($alert['route']); ?>" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-black shadow-sm hover:bg-white <?php echo e($alert['bg']); ?>">
                        <svg class="h-4 w-4 <?php echo e($alert['color']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($alert['icon']); ?>" />
                        </svg>
                        <span><?php echo e($alert['count']); ?> <?php echo e($alert['label']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $inventoryAlerts['out']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('inventory.index', ['stock' => 'out'])); ?>" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-black text-red-800 shadow-sm hover:bg-red-100">
                        Agotado: <?php echo e($p->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $inventoryAlerts['low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('inventory.index', ['stock' => 'low'])); ?>" class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-black text-amber-800 shadow-sm hover:bg-amber-100">
                        Stock bajo: <?php echo e($p->name); ?> (<?php echo e($p->stock); ?>/<?php echo e($p->stock_minimum); ?>)
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <section class="mt-3 rounded-lg border p-4 shadow-sm <?php echo e($moneyTone['panel']); ?>">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-black uppercase tracking-wider <?php echo e($moneyTone['badge']); ?>"><?php echo e($moneySummary['label']); ?></span>
                    <h2 class="mt-2 text-base font-black text-gray-950">Resumen de dinero</h2>
                </div>
                <a href="<?php echo e(route('shipments.index')); ?>" class="shrink-0 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-black text-gray-700 shadow-sm hover:bg-gray-50">Ver guias</a>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Creado en <?php echo e($rangeLabel); ?></p>
                    <p class="mt-1 text-2xl font-black text-gray-950">$<?php echo e(number_format($moneySummary['createdValue'], 0, ',', '.')); ?></p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Entregado</p>
                    <p class="mt-1 text-2xl font-black text-emerald-700">$<?php echo e(number_format($moneySummary['deliveredValue'], 0, ',', '.')); ?></p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Recaudo pendiente</p>
                    <p class="mt-1 text-2xl font-black <?php echo e($moneySummary['collectionOpen'] > 0 ? 'text-amber-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['collectionOpen'], 0, ',', '.')); ?></p>
                </div>
                <div class="rounded-lg border border-white/70 bg-white p-3">
                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-500">Dinero a vigilar</p>
                    <p class="mt-1 text-2xl font-black <?php echo e($moneySummary['moneyToWatch'] > 0 ? 'text-red-700' : 'text-gray-950'); ?>">$<?php echo e(number_format($moneySummary['moneyToWatch'], 0, ',', '.')); ?></p>
                </div>
            </div>
        </section>

        <section class="mt-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
            <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Estado de guias</h3>
            <div class="mt-2 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                <?php $__empty_1 = true; $__currentLoopData = $chartStatusDistribution['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background: <?php echo e($seg['color']); ?>"></span>
                                <p class="truncate text-xs font-bold text-gray-700"><?php echo e($seg['label']); ?></p>
                            </div>
                            <span class="shrink-0 text-xs font-black text-gray-950"><?php echo e($seg['count']); ?></span>
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-200">
                            <div class="h-1.5 rounded-full" style="width: <?php echo e(round(($seg['count'] / $statusTotal) * 100)); ?>%; background: <?php echo e($seg['color']); ?>"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm font-semibold text-gray-500">Todavia no hay guias.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="mt-3 flex flex-1 flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 text-sm font-black text-gray-950">
                    <span>Graficas y analisis</span>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-black text-gray-700"><?php echo e($rangeLabel); ?></span>
                </div>
                <div class="mt-3 grid flex-1 min-w-0 gap-3" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); grid-auto-rows: 1fr;">
                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Guias creadas</h3>
                        <?php $sd = $chartShipmentsByDay; ?>
                        <div class="mt-2 flex flex-1 items-end overflow-x-auto pb-1">
                            <div class="flex h-36 min-w-max items-end gap-2">
                                <?php $__currentLoopData = $sd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(10, round(($d['count'] / $sd['max']) * 110)); ?>
                                    <div class="flex h-full w-9 shrink-0 flex-col items-center justify-end gap-1">
                                        <span class="text-xs font-black text-gray-950"><?php echo e($d['count']); ?></span>
                                        <div class="w-full rounded-t-md bg-blue-700" style="height: <?php echo e($h); ?>px; min-height: 6px;"></div>
                                        <span class="text-center text-xs font-bold leading-tight text-gray-500"><?php echo e($d['label']); ?><br><?php echo e($d['full']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos por entregas</h3>
                        <?php $rd = $chartRevenueByDay; ?>
                        <div class="mt-2 flex flex-1 items-end overflow-x-auto pb-1">
                            <div class="flex h-36 min-w-max items-end gap-2">
                                <?php $__currentLoopData = $rd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $h = max(10, round(($d['revenue'] / $rd['max']) * 110)); ?>
                                    <div class="flex h-full w-10 shrink-0 flex-col items-center justify-end gap-1">
                                        <span class="max-w-full truncate text-[10px] font-black text-emerald-700">$<?php echo e(number_format($d['revenue'], 0, ',', '.')); ?></span>
                                        <div class="w-full rounded-t-md bg-emerald-600" style="height: <?php echo e($h); ?>px; min-height: 6px;"></div>
                                        <span class="text-center text-xs font-bold leading-tight text-gray-500"><?php echo e($d['label']); ?><br><?php echo e($d['full']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                        <?php $mt = $chartMonthlyTrend; ?>
                        <div class="mt-2 flex flex-1 items-end justify-around gap-3">
                            <?php $__currentLoopData = $mt['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $h = max(10, round(($m['count'] / $mt['max']) * 90)); ?>
                                <div class="flex h-36 flex-1 flex-col items-center justify-end gap-1">
                                    <span class="text-xs font-black text-gray-950"><?php echo e($m['count']); ?></span>
                                    <div class="w-full rounded-t-md bg-gray-900" style="height:<?php echo e($h); ?>px; min-height:6px;"></div>
                                    <span class="text-xs font-bold text-gray-500"><?php echo e($m['label']); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <?php if(! empty($chartTopProducts)): ?>
                        <div class="flex min-w-0 flex-col rounded-lg border border-gray-200 bg-white p-3">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                            <div class="mt-2 grid flex-1 content-center gap-2.5">
                                <?php $__currentLoopData = array_slice($chartTopProducts, 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div>
                                        <div class="flex items-baseline justify-between gap-2">
                                            <p class="truncate text-sm font-black text-gray-950" title="<?php echo e($p['name']); ?>"><?php echo e($p['name']); ?></p>
                                            <span class="shrink-0 text-xs font-black text-gray-500"><?php echo e($p['count']); ?></span>
                                        </div>
                                        <div class="mt-1 h-2 rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-blue-700" style="width: <?php echo e($p['pct']); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
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