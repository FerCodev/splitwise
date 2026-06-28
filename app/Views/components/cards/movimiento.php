<?php
use App\Services\UserColor;

$variant = $variant ?? 'feed';
$tipo = $tipo ?? 'gasto';
$descripcion = $descripcion ?? ($tipo === 'pago' ? 'Pago' : 'Gastito');
$monto = (float) ($monto ?? 0);
$fecha = $fecha ?? date('Y-m-d');
$persona = $persona ?? 'Usuario';
$categoria = $categoria ?? null;
$contexto = $contexto ?? null;
$wrap = $wrap ?? true;
$preview = $preview ?? false;
$esGasto = $tipo === 'gasto';

// Si el caller pasa colorKey y el movimiento es un gasto, se aplica
// esa paleta. Pagos, deudas y sistema conservan colores reservados
// sin importar el colorKey.
$colorKey = $esGasto ? ($colorKey ?? UserColor::DEFAULT_KEY) : null;
$colorInfo = $colorKey !== null ? UserColor::get($colorKey) : null;

// Pagos: color reservado payment (verde).
// Gastos sin colorKey o colorKey=auto: reservado system (azul claro).
// Gastos con colorKey valido: paleta del usuario.
if ($esGasto && $colorInfo !== null) {
    $bg     = $colorInfo['bg'];
    $border = $colorInfo['border'];
    $solid  = $colorInfo['solid'];
    $text   = $colorInfo['text'];
} else {
    $reserved = $esGasto ? UserColor::RESERVED['system'] : UserColor::RESERVED['payment'];
    $bg     = $reserved['bg'];
    $border = $reserved['border'];
    $solid  = $reserved['solid'];
    $text   = $reserved['text'];
}

$inicialPersona = mb_strtoupper(mb_substr($persona, 0, 1));
?>

<?php if ($wrap): ?>
<div class="report-movement-list <?= $preview ? 'catalog-list-preview' : '' ?>">
    <a href="#" class="report-movement-link">
<?php endif; ?>

<?php if ($variant === 'user_color'): ?>
    <div class="report-movement-card group-movement-user-card" style="background: <?= esc($bg) ?>; border-left: 3px solid <?= esc($border) ?>;">
        <div class="catalog-card-top">
            <div class="catalog-row min-width-0">
                <div class="group-movement-avatar"><?= esc($inicialPersona) ?></div>
                <div class="min-width-0">
                    <div>
                        <span class="badge me-1" style="background: <?= esc($solid) ?>; color: #fff;"><?= $esGasto ? 'Gastito' : 'Pago' ?></span>
                        <span class="fw-medium small"><?= esc($descripcion) ?></span>
                    </div>
                    <div class="text-muted small"><?= date('d/m/Y', strtotime($fecha)) ?> &middot; <?= esc($persona) ?></div>
                </div>
            </div>
            <span class="fw-bold small text-nowrap" style="color: <?= esc($solid) ?>;"><?= moneda($monto) ?></span>
        </div>
        <?php if ($categoria || $contexto): ?>
            <div class="text-muted small mt-1">
                <?php if ($categoria): ?><span class="badge bg-light text-dark me-1"><?= esc($categoria) ?></span><?php endif; ?>
                <?= esc((string) $contexto) ?>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($variant === 'compact'): ?>
    <div class="group-movement-compact-card" style="background: <?= esc($bg) ?>; border-left: 3px solid <?= esc($border) ?>;">
        <div class="min-width-0">
            <div>
                <span class="badge me-1" style="background: <?= esc($solid) ?>; color: #fff;"><?= $esGasto ? 'Gastito' : 'Pago' ?></span>
                <span class="fw-semibold small"><?= esc($descripcion) ?></span>
            </div>
            <div class="text-muted small"><?= date('d/m/Y', strtotime($fecha)) ?> &middot; <?= esc($persona) ?><?= $categoria ? ' &middot; ' . esc($categoria) : '' ?></div>
        </div>
        <span class="fw-bold small text-nowrap" style="color: <?= esc($solid) ?>;"><?= moneda($monto) ?></span>
    </div>
<?php else: ?>
    <div class="report-movement-card" style="background: <?= esc($bg) ?>; border-left: 3px solid <?= esc($border) ?>;">
        <div class="catalog-card-top">
            <div class="min-width-0">
                <span class="badge me-1" style="background: <?= esc($solid) ?>; color: #fff;"><?= $esGasto ? 'Gastito' : 'Pago' ?></span>
                <span class="fw-medium small"><?= esc($descripcion) ?></span>
                <?php if ($categoria): ?>
                    <span class="badge bg-light text-dark ms-1"><?= esc($categoria) ?></span>
                <?php endif; ?>
            </div>
            <span class="fw-bold small text-nowrap" style="color: <?= esc($solid) ?>;"><?= moneda($monto) ?></span>
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
