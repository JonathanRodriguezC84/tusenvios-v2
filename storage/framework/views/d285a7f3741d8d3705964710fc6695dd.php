<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <?php if($eyebrow ?? false): ?>
            <p class="text-xs font-black uppercase tracking-wider text-blue-700"><?php echo e($eyebrow); ?></p>
        <?php endif; ?>
        <h2 class="text-xl font-semibold leading-tight text-gray-900"><?php echo e($title); ?></h2>
        <?php if($description ?? false): ?>
            <p class="mt-1 max-w-2xl text-sm text-gray-500"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <?php if($actions ?? false): ?>
        <div class="flex flex-wrap gap-2">
            <?php echo e($actions); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/components/page-header.blade.php ENDPATH**/ ?>