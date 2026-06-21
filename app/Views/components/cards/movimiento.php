<?php
$variant = $variant ?? 'feed';
$tipo = $tipo ?? 'gasto';
$descripcion = $descripcion ?? ($tipo === 'pago' ? 'Pago' : 'Gasto');
$monto = (float) ($monto ?? 0);
$fecha = $fecha ?? date('Y-m-d');
$persona = $persona ?? 'Usuario';
$categoria = $categoria ?? null;
$contexto = $contexto ?? null;
$wrap = $wrap ?? true;
$preview = $preview ?? false;
$esGasto = $tipo === 'gasto';
$cardClase = $esGasto ? 'report-movement-expense movement-border-expense' : 'report-movement-payment movement-border-payment';
$badgeClase = $esGasto ? 'bg-primary' : 'bg-success';
$montoClase = $esGasto ? 'text-primary' : 'text-success';
$inicialPersona = mb_strtoupper(mb_substr($persona, 0, 1));
$personaLower = mb_strtolower($persona);
$personaClase = str_contains($personaLower, 'anto') ? 'group-movement-anto' : 'group-movement-fer';
?>

<?php if ($wrap): ?>
<div class="report-movement-list <?= $preview ? 'catalog-list-preview' : '' ?>">
    <a href="#" class="report-movement-link">
<?php endif; ?>

<?php if ($variant === 'user_color'): ?>
    <div class="report-movement-card group-movement-user-card <?= esc($personaClase) ?>">
        <div class="catalog-card-top">
            <div class="catalog-row min-width-0">
                <div class="group-movement-avatar"><?= esc($inicialPersona) ?></div>
                <div class="min-width-0">
                    <div>
                        <span class="badge <?= esc($badgeClase) ?> me-1"><?= $esGasto ? 'Gasto' : 'Pago' ?></span>
                        <span class="fw-medium small"><?= esc($descripcion) ?></span>
                    </div>
                    <div class="text-muted small"><?= date('d/m/Y', strtotime($fecha)) ?> &middot; <?= esc($persona) ?></div>
                </div>
            </div>
            <span class="fw-bold small <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
        </div>
        <?php if ($categoria || $contexto): ?>
            <div class="text-muted small mt-1">
                <?php if ($categoria): ?><span class="badge bg-light text-dark me-1"><?= esc($categoria) ?></span><?php endif; ?>
                <?= esc((string) $contexto) ?>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($variant === 'compact'): ?>
    <div class="group-movement-compact-card <?= esc($cardClase) ?>">
        <div class="min-width-0">
            <div>
                <span class="badge <?= esc($badgeClase) ?> me-1"><?= $esGasto ? 'Gasto' : 'Pago' ?></span>
                <span class="fw-semibold small"><?= esc($descripcion) ?></span>
            </div>
            <div class="text-muted small"><?= date('d/m/Y', strtotime($fecha)) ?> &middot; <?= esc($persona) ?><?= $categoria ? ' &middot; ' . esc($categoria) : '' ?></div>
        </div>
        <span class="fw-bold small <?= esc($montoClase) ?> text-nowrap"><?= moneda($monto) ?></span>
    </div>
<?php else: ?>
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
<?php endif; ?>

<?php if ($wrap): ?>
    </a>
</div>
<?php endif; ?>
