<?php
$porcentaje = min(100, max(0, (float) ($porcentaje ?? 0)));
$pagado = (float) ($pagado ?? 0);
$total = max(0, (float) ($total ?? 0));
$angulo = -90 + ($porcentaje * 1.8);
$variant = $variant ?? 'semicircle';
?>

<?php if ($variant === 'compact_dial'): ?>
<div class="catalog-gauge-dial" style="--gauge-percent: <?= round($porcentaje, 2) ?>%;">
    <div class="catalog-gauge-dial-ring">
        <div><strong><?= round($porcentaje) ?>%</strong><span>del total</span></div>
    </div>
    <div class="catalog-card-top mt-3"><span class="text-muted small">Vos pagaste</span><b class="text-primary"><?= moneda($pagado) ?></b></div>
    <div class="catalog-card-top"><span class="text-muted small">Total grupo</span><b><?= moneda($total) ?></b></div>
</div>
<?php elseif ($variant === 'scale_bar'): ?>
<div class="catalog-gauge-bar">
    <div class="catalog-card-top"><div><span class="text-muted small d-block">Tu aporte al gasto</span><strong><?= round($porcentaje) ?>%</strong></div><b class="text-primary"><?= moneda($pagado) ?></b></div>
    <div class="catalog-gauge-bar-track"><span style="width: <?= round($porcentaje, 2) ?>%;"></span><i style="left: <?= round($porcentaje, 2) ?>%;"></i></div>
    <div class="group-spend-gauge-scale"><span><?= moneda(0) ?></span><span><?= moneda($total) ?></span></div>
    <div class="text-muted small mt-2">La escala completa representa el total gastado del grupo.</div>
</div>
<?php elseif ($variant === 'segmented_arc'): ?>
<div class="catalog-gauge-segmented" style="--gauge-angle: <?= round($angulo, 2) ?>deg;">
    <div class="catalog-card-top"><span class="text-muted small">Aporte sobre total</span><b><?= round($porcentaje) ?>%</b></div>
    <div class="catalog-gauge-segmented-meter"><div class="catalog-gauge-segmented-arc"></div><div class="catalog-gauge-segmented-needle"></div><div class="catalog-gauge-segmented-hub"></div></div>
    <div class="catalog-gauge-ticks"><span><?= moneda(0) ?></span><span><?= moneda($total * 0.25) ?></span><span><?= moneda($total * 0.5) ?></span><span><?= moneda($total * 0.75) ?></span><span><?= moneda($total) ?></span></div>
    <div class="catalog-gauge-zone-labels"><span>Bajo</span><span>Medio</span><span>Alto</span></div>
</div>
<?php elseif ($variant === 'segmented_donut'): ?>
<div class="catalog-gauge-donut">
    <div class="catalog-gauge-donut-ring"><div><strong><?= round($porcentaje) ?>%</strong><span><?= moneda($pagado) ?></span></div></div>
    <div class="catalog-gauge-donut-scale"><span><?= moneda(0) ?></span><span><?= moneda($total * 0.5) ?></span><span><?= moneda($total) ?></span></div>
    <div class="catalog-gauge-zone-labels"><span>Bajo</span><span>Medio</span><span>Alto</span></div>
</div>
<?php elseif ($variant === 'clean_arc'): ?>
<div class="catalog-gauge-clean-arc" style="--gauge-angle: <?= round($angulo, 2) ?>deg;">
    <div class="catalog-gauge-clean-scale"><span><?= moneda(0) ?></span><span><?= moneda($total * 0.5) ?></span><span><?= moneda($total) ?></span></div>
    <div class="catalog-gauge-clean-meter"><div class="catalog-gauge-clean-band"></div><div class="catalog-gauge-clean-needle"></div></div>
    <div class="text-center"><strong class="text-primary"><?= round($porcentaje) ?>%</strong><div class="text-muted small">Vos pagaste <?= moneda($pagado) ?></div></div>
</div>
<?php elseif ($variant === 'milestone_ring'): ?>
<div class="catalog-gauge-milestone">
    <div class="catalog-gauge-milestone-head"><div><span class="text-muted small d-block">Aporte actual</span><strong><?= round($porcentaje) ?>%</strong></div><b class="text-primary"><?= moneda($pagado) ?></b></div>
    <div class="catalog-gauge-milestone-ring"><div><span><?= moneda(0) ?></span><span><?= moneda($total * 0.25) ?></span><span><?= moneda($total * 0.5) ?></span><span><?= moneda($total * 0.75) ?></span><span><?= moneda($total) ?></span></div></div>
</div>
<?php else: ?>
<div class="group-spend-gauge" style="--gauge-angle: <?= round($angulo, 2) ?>deg;">
    <div class="group-spend-gauge-copy">
        <span>Tu aporte al gasto</span>
        <strong><?= round($porcentaje) ?>%</strong>
    </div>
    <div class="group-spend-gauge-meter" aria-label="Vos pagaste <?= round($porcentaje) ?>% del total gastado del grupo">
        <div class="group-spend-gauge-arc"></div>
        <div class="group-spend-gauge-needle"></div>
        <div class="group-spend-gauge-hub"></div>
    </div>
    <div class="group-spend-gauge-scale">
        <span><?= moneda(0) ?></span>
        <span><?= moneda($total) ?></span>
    </div>
    <div class="group-spend-gauge-detail">
        <span>Vos pagaste <?= moneda($pagado) ?></span>
        <span>Total grupo <?= moneda($total) ?></span>
    </div>
</div>
<?php endif; ?>
