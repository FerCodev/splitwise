<?php
use App\Services\UserColor;

$variant = $variant ?? 'feed';
$tipo = $tipo ?? 'gasto';
$descripcion = $descripcion ?? ($tipo === 'pago' ? 'Pago' : 'Gastito');
$monto = (float) ($monto ?? 0);
$fecha = $fecha ?? date('Y-m-d');
$persona = $persona ?? 'Usuario';
$userId = (int) ($userId ?? 0);
$avatarFilename = $avatarFilename ?? null;
$avatarUpdatedAt = $avatarUpdatedAt ?? null;
$url = $url ?? null;
$categoria = isset($categoria) && (is_string($categoria) || is_numeric($categoria))
    ? trim((string) $categoria)
    : null;
$grupo = isset($grupo) && (is_string($grupo) || is_numeric($grupo))
    ? trim((string) $grupo)
    : null;
$participantes = isset($participantes) && is_numeric($participantes)
    ? (int) $participantes
    : null;
$categoria = $categoria !== '' ? $categoria : null;
$grupo = $grupo !== '' ? $grupo : null;
$colorKey = $colorKey ?? null;
$esGasto = $tipo === 'gasto';

$colorKey = $esGasto ? ($colorKey ?? UserColor::DEFAULT_KEY) : null;
$colorInfo = $colorKey !== null ? UserColor::get($colorKey) : null;

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


$cardContent = '';
if ($variant === 'user_color'):
    ob_start(); ?>
    <div class="report-movement-card group-movement-user-card" style="background: <?= esc($bg) ?>; border-left: 3px solid <?= esc($border) ?>;">
        <div class="catalog-card-top">
            <div class="catalog-row min-width-0">
                <?= view('components/avatar', [
                    'userId' => $userId, 'name' => $persona,
                    'avatarFilename' => $avatarFilename,
                    'avatarUpdatedAt' => $avatarUpdatedAt,
                    'size' => 40,
                ]) ?>
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
        <?php if ($categoria): ?>
            <div class="text-muted small mt-1">
                <span class="badge bg-light text-dark me-1"><?= esc($categoria) ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php $cardContent = ob_get_clean();
elseif ($variant === 'compact'):
    ob_start(); ?>
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
    <?php $cardContent = ob_get_clean();
else:
    ob_start(); ?>
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
        <?php if ($grupo !== null || $participantes !== null): ?>
            <div class="text-muted small mt-1">
                <?php if ($grupo !== null): ?>Grupo: <?= esc($grupo) ?><?php endif; ?>
                <?php if ($grupo !== null && $participantes !== null): ?> &middot; <?php endif; ?>
                <?php if ($participantes !== null): ?><?= $participantes ?> part.<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php $cardContent = ob_get_clean();
endif; ?>

<?php if ($url !== null): ?>
<a href="<?= esc($url, 'attr') ?>" class="report-movement-link"><?= $cardContent ?></a>
<?php else: ?>
<?= $cardContent ?>
<?php endif; ?>
