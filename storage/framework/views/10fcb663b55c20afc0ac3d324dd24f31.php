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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard','description' => 'Resumen de actividad, guias y rendimiento de tu negocio.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','description' => 'Resumen de actividad, guias y rendimiento de tu negocio.']); ?>
             <?php $__env->slot('eyebrow', null, []); ?> <?php echo e(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [del] YYYY')); ?> <?php $__env->endSlot(); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-2">
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
                    <a href="<?php echo e(route('shipments.create')); ?>" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm">
                        Crear guia
                    </a>
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
        <?php $rangeLabel = $dateRange['range'] === 'today' ? 'Hoy' : ($dateRange['range'] === '7d' ? 'Ultimos 7 dias' : ($dateRange['range'] === '30d' ? 'Ultimos 30 dias' : ($dateRange['range'] === '90d' ? 'Ultimos 90 dias' : $dateRange['from'] . ' - ' . $dateRange['to']))); ?>
        <?php if($onboarding['show']): ?>
            <section class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-700">Primeros pasos</p>
                        <h3 class="mt-1 text-base font-semibold text-gray-950">Completa tu cuenta</h3>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-800"><?php echo e($onboarding['completed']); ?>/<?php echo e($onboarding['total']); ?></span>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <?php $__currentLoopData = $onboarding['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($step['route']); ?>" class="rounded-md border <?php echo e($step['done'] ? 'border-emerald-200 bg-white' : 'border-blue-100 bg-white'); ?> p-3 hover:shadow-sm">
                            <p class="text-sm font-semibold text-gray-950"><?php echo e($step['done'] ? '✓' : $loop->iteration.'.'); ?> <?php echo e($step['label']); ?></p>
                            <p class="mt-0.5 text-xs text-gray-600"><?php echo e($step['description']); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if($trialGuideCounter): ?>
            <section class="mb-5 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-white p-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-700">Prueba gratis</p>
                        <h3 class="mt-1 text-base font-bold text-gray-950">Te quedan <?php echo e($trialGuideCounter['remaining']); ?> de <?php echo e($trialGuideCounter['total']); ?> guias</h3>
                        <?php if($trialGuideCounter['remaining'] <= 3): ?>
                            <p class="mt-0.5 text-sm text-gray-600">Crea algunas guias mas y activa tu plan mensual.</p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 rounded-full h-3 w-48 overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width:<?php echo e(($trialGuideCounter['total'] - $trialGuideCounter['remaining']) / $trialGuideCounter['total'] * 100); ?>%"></div>
                        </div>
                        <span class="text-sm font-bold text-blue-800"><?php echo e($trialGuideCounter['remaining']); ?>/<?php echo e($trialGuideCounter['total']); ?></span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase text-gray-500">Envios <span class="text-blue-700"><?php echo e($rangeLabel); ?></span></p>
                <p class="mt-1 text-3xl font-black text-gray-950"><?php echo e($metrics['shipments_today']); ?></p>
                <?php if($metrics['delta'] != 0): ?>
                    <p class="mt-0.5 text-xs font-semibold <?php echo e($metrics['delta'] > 0 ? 'text-emerald-600' : 'text-red-600'); ?>">
                        <?php echo e($metrics['delta'] > 0 ? '+' : ''); ?><?php echo e($metrics['delta']); ?> vs ayer
                    </p>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('shipments.index', ['status' => 'created'])); ?>" class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase text-gray-500">Por imprimir</p>
                <p class="mt-1 text-3xl font-black text-gray-950"><?php echo e($metrics['pending_print']); ?></p>
            </a>
            <a href="<?php echo e(route('shipments.index', ['status' => 'on_route'])); ?>" class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm hover:bg-gray-50">
                <p class="text-xs font-black uppercase text-gray-500">En transito</p>
                <p class="mt-1 text-3xl font-black text-gray-950"><?php echo e($metrics['in_transit']); ?></p>
            </a>
            <div class="rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs font-black uppercase text-gray-500">Ingresos <span class="text-blue-700"><?php echo e($rangeLabel); ?></span></p>
                <p class="mt-1 text-3xl font-black text-emerald-700">$<?php echo e(number_format((float) $metrics['revenue_today'], 0, ',', '.')); ?></p>
            </div>
        </section>

        <?php if(! empty($alerts)): ?>
            <div class="mt-5 flex flex-wrap gap-3">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($alert['route']); ?>" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:shadow-md transition-shadow <?php echo e($alert['bg']); ?>">
                        <svg class="h-4 w-4 <?php echo e($alert['color']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($alert['icon']); ?>" />
                        </svg>
                        <span><?php echo e($alert['count']); ?> <?php echo e($alert['label']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Envios <span class="text-blue-700"><?php echo e($rangeLabel); ?></span></h3>
                <?php $sd = $chartShipmentsByDay; ?>
                <div class="mt-3 flex items-end justify-around gap-1.5" style="height: 110px;">
                    <?php $__currentLoopData = $sd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $h = max(6, round(($d['count'] / $sd['max']) * 90)); ?>
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-gray-950"><?php echo e($d['count']); ?></span>
                            <div class="w-full rounded-sm transition-all hover:opacity-80" style="height: <?php echo e($h); ?>px; background: var(--te-button-color, #022a8c); min-height: 4px; border-radius: 3px 3px 0 0;"></div>
                            <span class="text-xs font-semibold text-gray-500"><?php echo e($d['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Ingresos <span class="text-blue-700"><?php echo e($rangeLabel); ?></span></h3>
                <?php $rd = $chartRevenueByDay; ?>
                <div class="mt-3 flex items-end justify-around gap-1.5" style="height: 110px;">
                    <?php $__currentLoopData = $rd['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $h = max(6, round(($d['revenue'] / $rd['max']) * 90)); ?>
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-emerald-700">$<?php echo e(number_format($d['revenue'], 0, ',', '.')); ?></span>
                            <div class="w-full rounded-sm transition-all hover:opacity-80" style="height: <?php echo e($h); ?>px; background: #10b981; min-height: 4px; border-radius: 3px 3px 0 0;"></div>
                            <span class="text-xs font-semibold text-gray-500"><?php echo e($d['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Distribucion de envios</h3>
                <?php $donut = $chartStatusDistribution; ?>
                <div class="mt-3 flex flex-col items-center gap-3 sm:flex-row sm:gap-4">
                    <div class="shrink-0" style="width: 90px; height: 90px; border-radius: 50%;
                        background: conic-gradient(
                            <?php $__currentLoopData = $donut['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($seg['color']); ?> <?php echo e($seg['start']); ?>deg <?php echo e($seg['end']); ?>deg<?php echo e(!$loop->last ? ',' : ''); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        );">
                    </div>
                    <div class="grid gap-1 text-xs">
                        <?php $__currentLoopData = $donut['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: <?php echo e($seg['color']); ?>"></span>
                                <span class="font-semibold text-gray-700"><?php echo e($seg['label']); ?></span>
                                <span class="font-bold text-gray-950"><?php echo e($seg['count']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>
        </div>

        <?php if(! empty($chartTopProducts)): ?>
            <section class="mt-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Productos mas enviados</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-5">
                    <?php $__currentLoopData = $chartTopProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="truncate text-sm font-semibold text-gray-950" title="<?php echo e($p['name']); ?>"><?php echo e($p['name']); ?></p>
                                <span class="shrink-0 text-xs font-bold text-gray-500"><?php echo e($p['count']); ?></span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full transition-all" style="width: <?php echo e($p['pct']); ?>%; background: var(--te-button-color, #022a8c); border-radius: 999px;"></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tendencia mensual</h3>
                <?php $mt = $chartMonthlyTrend; ?>
                <div class="mt-3 flex items-end justify-around gap-2" style="height: 100px;">
                    <?php $__currentLoopData = $mt['months']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $h = max(6, round(($m['count'] / $mt['max']) * 75)); ?>
                        <div class="flex flex-1 flex-col items-center gap-1 h-full justify-end">
                            <span class="text-xs font-bold text-gray-950"><?php echo e($m['count']); ?></span>
                            <div class="w-full" style="height:<?php echo e($h); ?>px; background:var(--te-button-color, #022a8c); border-radius:3px 3px 0 0; min-height:4px;"></div>
                            <span class="text-xs font-semibold text-gray-500"><?php echo e($m['label']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tasa de entrega</h3>
                <div class="mt-3 flex flex-col items-center gap-2">
                    <div class="text-4xl font-black <?php echo e($deliveryRate['rate'] >= 80 ? 'text-emerald-600' : ($deliveryRate['rate'] >= 50 ? 'text-amber-600' : 'text-red-600')); ?>"><?php echo e($deliveryRate['rate']); ?>%</div>
                    <p class="text-xs text-gray-500"><?php echo e($deliveryRate['delivered']); ?> de <?php echo e($deliveryRate['total']); ?> guias entregadas</p>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full <?php echo e($deliveryRate['rate'] >= 80 ? 'bg-emerald-500' : ($deliveryRate['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500')); ?>" style="width:<?php echo e($deliveryRate['rate']); ?>%"></div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Actividad reciente</h3>
                <div class="mt-2 divide-y divide-gray-100 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $recentAudit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between py-1.5 gap-2">
                            <p class="text-xs text-gray-700 truncate"><?php echo e(\Illuminate\Support\Str::limit($audit['description'], 40)); ?></p>
                            <span class="text-xs text-gray-400 shrink-0"><?php echo e(\Carbon\Carbon::parse($audit['date'])->diffForHumans(null, true)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-xs text-gray-500 py-2">Sin actividad reciente</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php if(! empty($inventoryAlerts['low']) || ! empty($inventoryAlerts['out'])): ?>
            <section class="mt-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Alertas de inventario</h3>
                <div class="mt-3 flex flex-wrap gap-3">
                    <?php $__currentLoopData = $inventoryAlerts['out']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('inventory.index', ['stock' => 'out'])); ?>" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100">
                            ⚠️ Agotado: <?php echo e($p->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $inventoryAlerts['low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('inventory.index', ['stock' => 'low'])); ?>" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                            Stock bajo: <?php echo e($p->name); ?> (<?php echo e($p->stock); ?>/<?php echo e($p->stock_minimum); ?>)
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
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