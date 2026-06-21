<?php
$tipo = $tipo ?? 'gasto';
$descripcion = $descripcion ?? ($tipo === 'pago' ? 'Pago' : 'Gasto');
$monto = (float) ($monto ?? 0);
$fecha = $fecha ?? date('Y-m-d');
$persona = $persona ?? 'Usuario';
$categoria = $categoria ?? null;
$contexto = $contexto ?? null;
$esGasto = $tipo === 'gasto';
$cardClase = $esGasto ? 'report-movement-expense catalog-border-primary' : 'report-movement-payment catalog-border-success';
$badgeClase = $esGasto ? 'bg-primary' : 'bg-success';
$montoClase = $esGasto ? 'text-primary' : 'text-success';
?>

<div class="report-movement-list catalog-list-preview">
    <a href="#" class="report-movement-link">
        <div class="report-movement-card <?= esc($cardClase) ?>">
            <div class="catalog-card-top">
                <div class="min-width-0">
                    <span class="badge <?= esc($badgeClase) ?> me-1"><?= $esGasto ? 'Gasto' : 'Pago' ?></span>
                    <span class="fw-medium small"><?= esc($descripcion) ?></span>
                    <?php if ($categoria): ?>
                        <span class="badge bg-light text-dark ms-1"><?= esc($categoria) ?></span>
                    <?php endif; ?>
                </div>
                <span class="fw-bold small <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
            </div>
            <div class="text-muted small mt-1"><?= date('d/m/Y', strtotime($fecha)) ?> &middot; <?= esc($persona) ?></div>
            <?php if ($contexto): ?>
                <div class="text-muted small"><?= esc($contexto) ?></div>
            <?php endif; ?>
        </div>
    </a>
</div>
