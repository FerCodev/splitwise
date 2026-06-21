<?php
$variant = $variant ?? 'operational';
$nombre = $nombre ?? 'Grupo';
$estado = $estado ?? 'activo';
$saldo = (float) ($saldo ?? 0);
$ultimoTipo = $ultimoTipo ?? null;
$ultimoDescripcion = $ultimoDescripcion ?? null;
$ultimoMonto = isset($ultimoMonto) ? (float) $ultimoMonto : null;
$ultimoFecha = $ultimoFecha ?? null;
$entrarUrl = $entrarUrl ?? '#';
$gastoUrl = $gastoUrl ?? null;
$inicial = mb_strtoupper(mb_substr($nombre, 0, 1));
$badgeClase = match ($estado) {
    'activo' => 'bg-success',
    'cerrado' => 'bg-warning text-dark',
    'liquidado' => 'bg-secondary',
    default => 'bg-secondary',
};
$saldoClase = $saldo > 0 ? 'text-success' : ($saldo < 0 ? 'text-danger' : 'text-muted');
$saldoTexto = moneda(abs($saldo));
?>

<?php if ($variant === 'balance_first'): ?>
    <div class="dash-card catalog-preview-card catalog-balance-first">
        <div class="dash-card-body text-center">
            <div class="small text-muted"><?= esc($nombre) ?> &middot; <?= esc(ucfirst($estado)) ?></div>
            <div class="catalog-balance-amount <?= esc($saldoClase) ?>"><?= $saldoTexto ?></div>
            <div class="small text-muted"><?= $saldo > 0 ? 'a favor' : ($saldo < 0 ? 'debe' : 'saldado') ?></div>
            <div class="catalog-actions justify-content-center mt-3">
                <a class="btn btn-outline-primary btn-sm" href="<?= esc($entrarUrl, 'attr') ?>">Entrar</a>
                <?php if ($estado === 'activo' && $gastoUrl): ?>
                    <a class="btn btn-primary btn-sm" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($variant === 'compact'): ?>
    <div class="dash-card catalog-preview-card">
        <a href="<?= esc($entrarUrl, 'attr') ?>" class="dash-list-item text-decoration-none">
            <div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm"><?= esc($inicial) ?></div>
            <div class="dash-list-item-info">
                <div class="dash-list-item-title"><?= esc($nombre) ?> <span class="badge <?= esc($badgeClase) ?> ms-1"><?= esc(ucfirst($estado)) ?></span></div>
                <div class="dash-list-item-subtitle"><?= $ultimoDescripcion ? 'Ultimo: ' . esc($ultimoDescripcion) : 'Sin movimientos recientes' ?></div>
            </div>
            <div class="dash-list-item-amount <?= esc($saldoClase) ?>"><?= $saldoTexto ?></div>
        </a>
        <?php if ($estado === 'activo' && $gastoUrl): ?>
            <div class="dash-card-body pt-0">
                <a class="btn btn-primary btn-sm w-100" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="dash-card catalog-preview-card">
        <div class="dash-card-body">
            <div class="catalog-row align-items-start">
                <div class="catalog-avatar catalog-avatar-primary"><?= esc($inicial) ?></div>
                <div class="min-width-0 flex-grow-1">
                    <div class="catalog-title-row">
                        <strong><?= esc($nombre) ?></strong>
                        <span class="badge <?= esc($badgeClase) ?>"><?= esc(ucfirst($estado)) ?></span>
                    </div>
                    <div class="text-muted small">Saldo: <span class="financial-amount <?= esc($saldoClase) ?>"><?= $saldoTexto ?></span></div>
                    <?php if ($ultimoDescripcion): ?>
                        <div class="text-muted small mt-1">
                            <span class="badge <?= $ultimoTipo === 'pago' ? 'bg-success' : 'bg-primary' ?>"><?= $ultimoTipo === 'pago' ? 'Pago' : 'Gasto' ?></span>
                            <?= esc($ultimoDescripcion) ?>
                            <?php if ($ultimoMonto !== null): ?><span class="financial-amount float-end"><?= moneda($ultimoMonto) ?></span><?php endif; ?>
                        </div>
                        <?php if ($ultimoFecha): ?><div class="text-muted small mt-1"><?= date('d/m/Y', strtotime($ultimoFecha)) ?></div><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="catalog-actions mt-3">
                <a class="btn btn-outline-primary btn-sm" href="<?= esc($entrarUrl, 'attr') ?>">Entrar</a>
                <?php if ($estado === 'activo' && $gastoUrl): ?>
                    <a class="btn btn-primary btn-sm" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


