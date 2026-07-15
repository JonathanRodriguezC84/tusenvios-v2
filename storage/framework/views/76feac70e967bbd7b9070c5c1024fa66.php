<?php
    $template = in_array($template ?? ($brand['template'] ?? 'classic'), ['classic', 'modern', 'advance'], true)
        ? ($template ?? $brand['template'])
        : 'classic';
    $demo = [
        'guide' => 'TEV202600001',
        'barcode' => 'TEV202600001',
        'recipient_name' => 'SOFIA CARDENAS',
        'recipient_address' => 'CALLE 140 #12-44',
        'recipient_zone' => 'CEDRITOS / BOGOTA',
        'recipient_phone' => '3203332211',
        'sender_address' => data_get($brand, 'address') ?: 'DIRECCION NO CONFIGURADA',
        'sender_zone' => trim((data_get($brand, 'neighborhood') ?: 'SIN BARRIO').' / '.(data_get($brand, 'locality') ?: 'SIN CIUDAD'), ' /'),
        'notes' => 'ENTREGAR EN HORARIO DE OFICINA',
        'zone' => 'NORTE',
        'pieces' => '1',
        'collection' => '$74.000',
    ];
    $socialLinks = collect([
        ['type' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $brand['whatsapp'] ?? null],
        ['type' => 'instagram', 'label' => 'Instagram', 'value' => $brand['instagram'] ?? null],
        ['type' => 'facebook', 'label' => 'Facebook', 'value' => $brand['facebook'] ?? null],
        ['type' => 'tiktok', 'label' => 'TikTok', 'value' => $brand['tiktok'] ?? null],
    ])->filter(fn ($item) => filled($item['value']))->values();
    $socials = $socialLinks->isNotEmpty() ? $socialLinks : collect([
        ['type' => 'whatsapp', 'label' => 'WhatsApp', 'value' => data_get($brand, 'phone') ?: data_get($brand, 'whatsapp', 'NO REGISTRADO')],
        ['type' => 'instagram', 'label' => 'Instagram', 'value' => data_get($brand, 'instagram', '@TUSENVIOS')],
    ]);
    $brandName = $brand['name'] ?: 'TUS ENVIOS';
    $brandPhone = data_get($brand, 'phone') ?: ($brand['whatsapp'] ?: 'NO REGISTRADO');
    $brandMessage = $brand['message'] ?: 'GRACIAS POR TU COMPRA';

    $topBlock = function () use ($logoUrl, $brandName, $demo, $brandPhone, $brandMessage) {
        return view('brand-settings.partials.label-blocks-top', compact('logoUrl', 'brandName', 'demo', 'brandPhone', 'brandMessage'))->render();
    };
?>

<?php if (! $__env->hasRenderedOnce('6351c511-438d-47f2-9d75-e133b2503719')): $__env->markAsRenderedOnce('6351c511-438d-47f2-9d75-e133b2503719'); ?>
    <?php
        $blocksDir = resource_path('views/brand-settings/partials');
        if (! file_exists($blocksDir.'/label-blocks-top.blade.php')) {
            file_put_contents($blocksDir.'/label-blocks-top.blade.php', <<<'TOP'
<section class="label-top">
    <div class="label-logo">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $brandName }}">
        @else
            <span>{{ strtoupper(substr($brandName, 0, 2)) }}</span>
        @endif
    </div>
    <div class="label-sender">
        <p class="label-company">{{ $brandName }}</p>
        <p>{{ $demo['sender_address'] }}</p>
        <p>{{ $demo['sender_zone'] }}</p>
        <p>{{ $brandPhone }}</p>
        <p class="label-message">{{ $brandMessage }}</p>
    </div>
</section>
TOP);
        }
    ?>
<?php endif; ?>

<article class="label-sheet label-<?php echo e($template); ?>">
    <?php if($template === 'advance'): ?>
        <section class="label-barcode"><p class="label-guide"><?php echo e($demo['guide']); ?></p><div class="label-code"><?php echo \App\Support\Code39Barcode::svg($demo['barcode'], 68); ?></div></section>
    <?php endif; ?>

    <?php echo $topBlock(); ?>


    <section class="label-socials">
        <?php $__currentLoopData = $socials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span>
    <i class="<?php echo e(data_get($social, 'type', strtolower(data_get($social, 'icon', 'social')))); ?>" aria-label="<?php echo e($social['label'] ?? ($social['icon'] ?? 'Red social')); ?>">
        <?php ($socialType = $social['type'] ?? strtolower($social['icon'] ?? '')); ?>
        <?php if($socialType === 'whatsapp' || $socialType === 'wa'): ?>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.57 2 2.13 6.35 2.13 11.71c0 1.72.47 3.39 1.35 4.86L2 22l5.6-1.43a10.1 10.1 0 0 0 4.44.99c5.47 0 9.91-4.35 9.91-9.7C21.95 6.35 17.51 2 12.04 2Zm5.75 13.73c-.24.67-1.38 1.27-1.95 1.35-.5.08-1.14.11-1.84-.11-.43-.13-.98-.32-1.68-.62-2.95-1.25-4.88-4.15-5.03-4.34-.15-.2-1.2-1.56-1.2-2.98s.76-2.12 1.03-2.41c.27-.29.59-.36.79-.36h.57c.18.01.43-.07.67.5.24.56.82 1.95.9 2.09.07.15.12.32.02.51-.1.2-.15.31-.3.48-.15.17-.32.38-.45.51-.15.15-.31.32-.13.61.18.29.8 1.29 1.72 2.08 1.18 1.03 2.18 1.35 2.49 1.5.31.15.49.13.67-.08.18-.2.77-.88.98-1.18.2-.29.41-.24.69-.15.29.1 1.83.85 2.14 1 .31.15.51.22.59.34.08.13.08.74-.16 1.41Z"/></svg>
        <?php elseif($socialType === 'instagram' || $socialType === 'ig'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
        <?php elseif($socialType === 'facebook' || $socialType === 'fb'): ?>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.4V6.7c0-.8.5-1 1.1-1H17V2.4c-.9-.1-1.8-.2-2.7-.2-2.7 0-4.5 1.6-4.5 4.5v1.7H7v3.7h2.8V22H14v-9.9h2.8l.5-3.7H14Z"/></svg>
        <?php else: ?>
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3.2c.5 2 1.7 3.2 3.7 3.4v3.6a7 7 0 0 1-3.7-1.1v5.8c0 4-2.6 6.4-6.3 6.4-3 0-5.5-2-5.5-5.1 0-3.5 3-5.7 6.5-5.1v3.8c-1.4-.5-2.6.2-2.6 1.3 0 1 .8 1.6 1.7 1.6 1.1 0 1.9-.7 1.9-2.3V3.2h4.3Z"/></svg>
        <?php endif; ?>
    </i><?php echo e(data_get($social, 'value')); ?>

</span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <?php if($template === 'classic'): ?>
        <section class="label-barcode"><p class="label-guide"><?php echo e($demo['guide']); ?></p><div class="label-code"><?php echo \App\Support\Code39Barcode::svg($demo['barcode'], 68); ?></div></section>
    <?php endif; ?>

    <section class="label-recipient-row">
        <div class="label-recipient">
            <div><span>NOMBRE</span><strong><?php echo e($demo['recipient_name']); ?></strong></div>
            <div><span>DIRECCION</span><strong class="big"><?php echo e($demo['recipient_address']); ?></strong></div>
            <div><span>BARRIO / LOCALIDAD</span><strong><?php echo e($demo['recipient_zone']); ?></strong></div>
            <div><span>TELEFONO</span><strong><?php echo e($demo['recipient_phone']); ?></strong></div>
        </div>
        <div class="label-qr"><?php echo \App\Support\QrCode::svg($demo['barcode'], 3); ?></div>
    </section>

    <section class="label-notes"><span>OBSERVACIONES</span><strong><?php echo e($demo['notes']); ?></strong></section>

    <?php if($template === 'modern'): ?>
        <section class="label-barcode"><p class="label-guide"><?php echo e($demo['guide']); ?></p><div class="label-code"><?php echo \App\Support\Code39Barcode::svg($demo['barcode'], 68); ?></div></section>
    <?php endif; ?>

    <section class="label-metas">
        <div><span>ZONA</span><strong><?php echo e($demo['zone']); ?></strong></div>
        <div><span>PIEZAS</span><strong><?php echo e($demo['pieces']); ?></strong></div>
        <div><span>RECAUDO</span><strong><?php echo e($demo['collection']); ?></strong></div>
    </section>
    <section class="label-footer"><span>TUSENVIOS.COM.CO</span><span><?php echo e($demo['barcode']); ?></span><span><?php echo e(now()->format('Y-m-d H:i')); ?></span></section>
</article><?php /**PATH C:\Users\Rci Shop\Herd\tusenvios_local\resources\views/brand-settings/partials/label-demo.blade.php ENDPATH**/ ?>