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
    <div class="dash-card catalog-balance-first">
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
<?php elseif ($variant === 'action_large'): ?>
    <div class="dash-card group-card-action-large">
        <div class="dash-card-body">
            <div class="catalog-title-row align-items-start">
                <div class="catalog-row min-width-0">
                    <div class="catalog-avatar catalog-avatar-primary"><?= esc($inicial) ?></div>
                    <div class="min-width-0">
                        <strong class="d-block text-truncate"><?= esc($nombre) ?></strong>
                        <span class="badge <?= esc($badgeClase) ?> mt-1"><?= esc(ucfirst($estado)) ?></span>
                    </div>
                </div>
                <div class="text-end">
                    <span class="text-muted small d-block">Saldo</span>
                    <strong class="financial-amount <?= esc($saldoClase) ?>"><?= $saldoTexto ?></strong>
                </div>
            </div>
            <?php if ($ultimoDescripcion): ?>
                <div class="group-card-soft-row mt-3">
                    <span class="badge <?= $ultimoTipo === 'pago' ? 'bg-success' : 'bg-primary' ?>"><?= $ultimoTipo === 'pago' ? 'Pago' : 'Gasto' ?></span>
                    <span class="text-truncate"><?= esc($ultimoDescripcion) ?></span>
                    <?php if ($ultimoMonto !== null): ?><b class="financial-amount"><?= moneda($ultimoMonto) ?></b><?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="group-card-big-actions mt-3">
                <a class="btn btn-outline-primary" href="<?= esc($entrarUrl, 'attr') ?>">Entrar</a>
                <?php if ($estado === 'activo' && $gastoUrl): ?>
                    <a class="btn btn-primary" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($variant === 'activity_split'): ?>
    <div class="dash-card group-card-activity-split">
        <div class="dash-card-body">
            <div class="catalog-card-top">
                <div>
                    <strong><?= esc($nombre) ?></strong>
                    <div class="text-muted small"><?= esc(ucfirst($estado)) ?></div>
                </div>
                <div class="group-card-balance-pill <?= esc($saldoClase) ?>"><?= $saldoTexto ?></div>
            </div>
            <div class="group-card-activity-box mt-3">
                <span class="text-muted small">Ultimo movimiento</span>
                <?php if ($ultimoDescripcion): ?>
                    <div class="catalog-card-top mt-1">
                        <div class="min-width-0">
                            <span class="badge <?= $ultimoTipo === 'pago' ? 'bg-success' : 'bg-primary' ?> me-1"><?= $ultimoTipo === 'pago' ? 'Pago' : 'Gasto' ?></span>
                            <strong class="small"><?= esc($ultimoDescripcion) ?></strong>
                        </div>
                        <?php if ($ultimoMonto !== null): ?><b class="financial-amount small"><?= moneda($ultimoMonto) ?></b><?php endif; ?>
                    </div>
                    <?php if ($ultimoFecha): ?><div class="text-muted small mt-1"><?= date('d/m/Y', strtotime($ultimoFecha)) ?></div><?php endif; ?>
                <?php else: ?>
                    <strong class="small d-block mt-1">Sin movimientos recientes</strong>
                <?php endif; ?>
            </div>
            <div class="catalog-actions mt-3">
                <a class="btn btn-outline-primary btn-sm" href="<?= esc($entrarUrl, 'attr') ?>">Entrar</a>
                <?php if ($estado === 'activo' && $gastoUrl): ?>
                    <a class="btn btn-primary btn-sm" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($variant === 'minimal_panel'): ?>
    <div class="dash-card group-card-minimal-panel">
        <a href="<?= esc($entrarUrl, 'attr') ?>" class="group-card-minimal-link">
            <div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm"><?= esc($inicial) ?></div>
            <div class="min-width-0">
                <strong class="d-block text-truncate"><?= esc($nombre) ?></strong>
                <span class="text-muted small"><?= $ultimoDescripcion ? esc($ultimoDescripcion) : 'Sin movimientos recientes' ?></span>
            </div>
            <strong class="financial-amount <?= esc($saldoClase) ?>"><?= $saldoTexto ?></strong>
        </a>
        <?php if ($estado === 'activo' && $gastoUrl): ?>
            <div class="group-card-minimal-footer">
                <a class="btn btn-primary btn-sm w-100" href="<?= esc($gastoUrl, 'attr') ?>">+ Gasto</a>
            </div>
        <?php endif; ?>
    </div>
<?php elseif ($variant === 'compact'): ?>
    <div class="dash-card">
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
    <div class="dash-card">
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


