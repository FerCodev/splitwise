<?php
$titulo = $titulo ?? 'Resumen';
$monto = (float) ($monto ?? 0);
$detalle = $detalle ?? null;
$color = $color ?? 'text-primary';
?>

<div class="dash-card catalog-preview-card">
    <div class="dash-card-body">
        <div class="balance-strip-label"><?= esc($titulo) ?></div>
        <div class="balance-strip-amount <?= esc($color) ?>"><?= moneda($monto) ?></div>
        <?php if ($detalle): ?>
            <div class="text-muted small"><?= esc($detalle) ?></div>
        <?php endif; ?>
    </div>
</div>
