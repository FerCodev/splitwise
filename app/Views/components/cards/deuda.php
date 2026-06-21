<?php
$modo = $modo ?? 'debes';
$persona = $persona ?? 'Persona';
$grupo = $grupo ?? 'Grupo';
$monto = (float) ($monto ?? 0);
$esDeudaPropia = $modo === 'debes';
$cardClase = $esDeudaPropia ? 'home-debt-card-owes' : 'home-debt-card-owed';
$montoClase = $esDeudaPropia ? 'text-danger' : 'text-success';
?>

<div class="report-movement-list catalog-list-preview">
    <a href="#" class="report-movement-link">
        <div class="report-movement-card home-debt-card <?= esc($cardClase) ?>">
            <div class="catalog-card-top">
                <div class="min-width-0">
                    <span class="badge me-1"><?= $esDeudaPropia ? 'Deb&eacute;s' : 'Te deben' ?></span>
                    <span class="fw-medium small"><?= esc($persona) ?></span>
                </div>
                <span class="fw-bold small <?= esc($montoClase) ?>"><?= moneda($monto) ?></span>
            </div>
            <div class="text-muted small mt-1"><?= esc($grupo) ?> &middot; <?= $esDeudaPropia ? 'Pago pendiente' : 'Cobro pendiente' ?></div>
        </div>
    </a>
</div>
