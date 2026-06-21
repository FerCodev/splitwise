<?php
$nombre = $nombre ?? 'Grupo';
$estado = $estado ?? 'activo';
$saldo = (float) ($saldo ?? 0);
$ultimoTipo = $ultimoTipo ?? null;
$ultimoDescripcion = $ultimoDescripcion ?? null;
$inicial = mb_strtoupper(mb_substr($nombre, 0, 1));
$badgeClase = match ($estado) {
    'activo' => 'bg-success',
    'cerrado' => 'bg-warning text-dark',
    'liquidado' => 'bg-secondary',
    default => 'bg-secondary',
};
$saldoClase = $saldo > 0 ? 'text-success' : ($saldo < 0 ? 'text-danger' : 'text-muted');
?>

<div class="dash-card catalog-preview-card">
    <div class="dash-card-body">
        <div class="catalog-row align-items-start">
            <div class="catalog-avatar catalog-avatar-primary"><?= esc($inicial) ?></div>
            <div class="min-width-0 flex-grow-1">
                <div class="catalog-title-row">
                    <strong><?= esc($nombre) ?></strong>
                    <span class="badge <?= esc($badgeClase) ?>"><?= esc(ucfirst($estado)) ?></span>
                </div>
                <div class="text-muted small">Saldo: <span class="financial-amount <?= esc($saldoClase) ?>"><?= moneda(abs($saldo)) ?></span></div>
                <?php if ($ultimoDescripcion): ?>
                    <div class="text-muted small mt-1">
                        <span class="badge <?= $ultimoTipo === 'pago' ? 'bg-success' : 'bg-primary' ?>"><?= $ultimoTipo === 'pago' ? 'Pago' : 'Gasto' ?></span>
                        <?= esc($ultimoDescripcion) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="catalog-actions mt-3">
            <button class="btn btn-outline-primary btn-sm" type="button">Entrar</button>
            <?php if ($estado === 'activo'): ?>
                <button class="btn btn-primary btn-sm" type="button">+ Gasto</button>
            <?php endif; ?>
        </div>
    </div>
</div>
