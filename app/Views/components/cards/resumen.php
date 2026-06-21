<?php
$variant = $variant ?? 'simple';
$titulo = $titulo ?? 'Resumen';
$monto = (float) ($monto ?? 0);
$detalle = $detalle ?? null;
$color = $color ?? 'text-primary';
$secundarios = $secundarios ?? [];
?>

<?php if ($variant === 'detail'): ?>
    <div class="dash-card catalog-preview-card">
        <div class="dash-card-body catalog-metric-grid">
            <div><small><?= esc($titulo) ?></small><b class="<?= esc($color) ?>"><?= moneda($monto) ?></b></div>
            <?php foreach (array_slice($secundarios, 0, 3) as $item): ?>
                <div><small><?= esc($item['label'] ?? '') ?></small><b class="<?= esc($item['color'] ?? '') ?>"><?= esc($item['value'] ?? '') ?></b></div>
            <?php endforeach; ?>
            <?php if (empty($secundarios)): ?>
                <div><small>Registros</small><b>-</b></div>
                <div><small>Periodo</small><b>Actual</b></div>
                <div><small>Filtro</small><b>Activo</b></div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif ($variant === 'compare'): ?>
    <div class="dash-card catalog-preview-card">
        <div class="dash-card-body">
            <div class="catalog-compare">
                <div><span class="status-dot status-dot-danger"></span><small><?= esc($titulo) ?></small><b class="<?= esc($color) ?>"><?= moneda($monto) ?></b></div>
                <div><span class="status-dot status-dot-active"></span><small>Estado</small><b><?= esc($detalle ?? 'Filtro aplicado') ?></b></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="dash-card catalog-preview-card">
        <div class="dash-card-body">
            <div class="balance-strip-label"><?= esc($titulo) ?></div>
            <div class="balance-strip-amount <?= esc($color) ?>"><?= moneda($monto) ?></div>
            <?php if ($detalle): ?>
                <div class="text-muted small"><?= esc($detalle) ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
