<section class="label-top">
    <div class="label-logo">
        <?php if($logoUrl): ?>
            <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($brandName); ?>">
        <?php else: ?>
            <span><?php echo e(strtoupper(substr($brandName, 0, 2))); ?></span>
        <?php endif; ?>
    </div>
    <div class="label-sender">
        <p class="label-company"><?php echo e($brandName); ?></p>
        <p><?php echo e($demo['sender_address']); ?></p>
        <p><?php echo e($demo['sender_zone']); ?></p>
        <p><?php echo e($brandPhone); ?></p>
        <p class="label-message"><?php echo e($brandMessage); ?></p>
    </div>
</section><?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/brand-settings/partials/label-blocks-top.blade.php ENDPATH**/ ?>