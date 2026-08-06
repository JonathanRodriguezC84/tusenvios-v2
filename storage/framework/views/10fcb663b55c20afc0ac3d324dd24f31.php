<?php
    $rangeLabel = $dateRange['label'] ?? 'Periodo';
    $catColors = [1 => '#2a78d6', 2 => '#1baf7a', 3 => '#eda100', 4 => '#008300', 5 => '#4a3aa7', 6 => '#e34948', 7 => '#e87ba4', 8 => '#eb6834'];
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard','description' => 'Tus envios de un vistazo.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','description' => 'Tus envios de un vistazo.']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <select name="range" id="dash-range" class="appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <option value="today" <?php echo e($dateRange['range'] === 'today' ? 'selected' : ''); ?>>Hoy</option>
                            <option value="7d" <?php echo e($dateRange['range'] === '7d' ? 'selected' : ''); ?>>Ultimos 7 dias</option>
                            <option value="30d" <?php echo e($dateRange['range'] === '30d' ? 'selected' : ''); ?>>Ultimos 30 dias</option>
                            <option value="90d" <?php echo e($dateRange['range'] === '90d' ? 'selected' : ''); ?>>Ultimos 90 dias</option>
                            <option value="custom" <?php echo e(!in_array($dateRange['range'], ['today','7d','30d','90d']) ? 'selected' : ''); ?>>Personalizado</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4 4 4-4" /></svg>
                    </div>
                    <div id="dash-dates" class="hidden items-center gap-2">
                        <input type="date" name="from" value="<?php echo e($dateRange['from']); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <span class="text-sm text-gray-500">a</span>
                        <input type="date" name="to" value="<?php echo e($dateRange['to']); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    </div>
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Aplicar</button>
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
            <div class="mb-3 flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5">
                <p class="text-sm font-semibold text-amber-800">
                    <?php echo e($operationHealth['stale']); ?> guia<?php echo e($operationHealth['stale'] === 1 ? '' : 's'); ?> sin actualizar en mas de 24 horas
                </p>
                <a href="<?php echo e(route('daily-tasks.index')); ?>" class="shrink-0 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700">
                    Actualizar
                </a>
            </div>
        <?php endif; ?>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <a href="<?php echo e(route('shipments.index')); ?>" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 hover:shadow">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Guias creadas</p>
                    <svg class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
                <p class="mt-3 text-4xl font-black text-gray-950"><?php echo e($metrics['shipments_today']); ?></p>
                <p class="mt-2 text-sm font-medium text-gray-400"><?php echo e($rangeLabel); ?></p>
            </a>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Entregadas</p>
                <p class="mt-3 text-4xl font-black <?php echo e($deliveryRate['total'] === 0 ? 'text-gray-300' : ($deliveryRate['rate'] >= 80 ? 'text-emerald-600' : ($deliveryRate['rate'] >= 50 ? 'text-amber-600' : 'text-red-600'))); ?>">
                    <?php echo e($deliveryRate['total'] === 0 ? '-' : $deliveryRate['rate'].'%'); ?>

                </p>
                <p class="mt-2 text-sm font-medium text-gray-400"><?php echo e($deliveryRate['delivered']); ?> de <?php echo e($deliveryRate['total']); ?> guias</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Dinero por cobrar</p>
                <p class="mt-3 text-4xl font-black <?php echo e($moneySummary['collectionOpen'] > 0 ? 'text-gray-900' : 'text-gray-300'); ?>">$<?php echo e(number_format($moneySummary['collectionOpen'], 0, ',', '.')); ?></p>
                <p class="mt-2 text-sm font-medium text-gray-400">Recaudo pendiente</p>
            </div>
        </section>

        <?php
            $chartData = collect($chartShipmentsByDay['days'])->map(fn ($d) => [
                'label' => $d['full'], 'sub' => ucfirst($d['label']).' '.$d['full'], 'value' => $d['count'],
            ])->all();
        ?>

        <section class="mt-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-gray-900">Guias <?php echo e($rangeLabel); ?></h2>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                    <?php echo e(array_sum(array_column($chartData, 'value'))); ?> en total
                </span>
            </div>
            <div class="mt-4">
                <?php if (isset($component)) { $__componentOriginal7f5eee30baa85644e054dd78a51aa94c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7f5eee30baa85644e054dd78a51aa94c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.charts.column-chart','data' => ['data' => $chartData,'color' => '#2a78d6','format' => 'number']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('charts.column-chart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($chartData),'color' => '#2a78d6','format' => 'number']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7f5eee30baa85644e054dd78a51aa94c)): ?>
<?php $attributes = $__attributesOriginal7f5eee30baa85644e054dd78a51aa94c; ?>
<?php unset($__attributesOriginal7f5eee30baa85644e054dd78a51aa94c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7f5eee30baa85644e054dd78a51aa94c)): ?>
<?php $component = $__componentOriginal7f5eee30baa85644e054dd78a51aa94c; ?>
<?php unset($__componentOriginal7f5eee30baa85644e054dd78a51aa94c); ?>
<?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        const rangeSelect = document.getElementById('dash-range');
        const datesDiv = document.getElementById('dash-dates');
        if (rangeSelect && datesDiv) {
            function toggleDates() {
                datesDiv.classList.toggle('hidden', rangeSelect.value !== 'custom');
                datesDiv.classList.toggle('flex', rangeSelect.value === 'custom');
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
<?php endif; ?><?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/dashboard.blade.php ENDPATH**/ ?>