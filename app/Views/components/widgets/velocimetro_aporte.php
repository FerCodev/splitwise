<?php
$porcentaje = min(100, max(0, (float) ($porcentaje ?? 0)));
$pagado = (float) ($pagado ?? 0);
$total = max(0, (float) ($total ?? 0));
$angulo = -90 + ($porcentaje * 1.8);
?>

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
