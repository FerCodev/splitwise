<?php
$variant = $variant ?? 'soft';
$modo = $modo ?? 'debes';
$persona = $persona ?? 'Persona';
$userId = (int) ($userId ?? 0);
$avatarFilename = $avatarFilename ?? null;
$avatarUpdatedAt = $avatarUpdatedAt ?? null;
$grupo = $grupo ?? 'Grupo';
$monto = (float) ($monto ?? 0);
$wrap = $wrap ?? true;
$preview = $preview ?? false;
$esDeudaPropia = $modo === 'debes';
$cardClase = $esDeudaPropia ? 'home-debt-card-owes' : 'home-debt-card-owed';
$montoClase = $esDeudaPropia ? 'text-danger' : 'text-success';
$badge = $esDeudaPropia ? 'Deb&eacute;s' : 'Te deben';
$detalle = $esDeudaPropia ? 'Pago pendiente' : 'Cobro pendiente';
?>

<?php if ($wrap): ?>
<div class="report-movement-list <?= $preview ? 'catalog-list-preview' : '' ?>">
    <a href="#" class="report-movement-link">
<?php endif; ?>

<?php if ($variant === 'direct_action'): ?>
    <div class="home-debt-action-card <?= esc($cardClase) ?>">
        <div class="catalog-card-top">
            <div class="min-width-0">
                <span class="badge"><?= $badge ?></span>
                <div class="fw-semibold mt-1"><?= esc($persona) ?></div>
                <div class="text-muted small"><?= esc($grupo) ?> &middot; <?= $detalle ?></div>
            </div>
            <span class="financial-amount <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
        </div>
        <span class="btn btn-sm <?= $esDeudaPropia ? 'btn-danger' : 'btn-success' ?> w-100 mt-3"><?= $esDeudaPropia ? 'Ver pago' : 'Ver cobro' ?></span>
    </div>
<?php elseif ($variant === 'person_summary'): ?>
    <div class="home-debt-person-card <?= esc($cardClase) ?>">
        <?= view('components/avatar', [
            'userId' => $userId, 'name' => $persona,
            'avatarFilename' => $avatarFilename,
            'avatarUpdatedAt' => $avatarUpdatedAt,
            'size' => 48,
        ]) ?>
        <div class="min-width-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge"><?= $badge ?></span>
                <strong><?= esc($persona) ?></strong>
            </div>
            <div class="text-muted small"><?= esc($grupo) ?> &middot; <?= $detalle ?></div>
        </div>
        <span class="financial-amount <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
    </div>
<?php else: ?>
    <div class="report-movement-card home-debt-card <?= esc($cardClase) ?>">
        <div class="catalog-card-top">
            <div class="min-width-0">
                <span class="badge me-1"><?= $badge ?></span>
                <span class="fw-medium small"><?= esc($persona) ?></span>
            </div>
            <span class="fw-bold small <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
        </div>
        <div class="text-muted small mt-1"><?= esc($grupo) ?> &middot; <?= $detalle ?></div>
    </div>
<?php endif; ?>

<?php if ($wrap): ?>
    </a>
</div>
<?php endif; ?>
