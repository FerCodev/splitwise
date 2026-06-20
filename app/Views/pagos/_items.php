<?php foreach ($pagos as $pago): ?>
    <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-payment mobile-transaction-link" style="border-left: 3px solid #16a34a;">
        <div class="card-body mobile-transaction-body">
            <div class="mobile-transaction-main">
                <div class="mobile-transaction-info">
                    <div class="fw-medium mobile-transaction-title"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                    <div class="text-muted small mt-1">
                        <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                        <?= esc($pago['pagador_nombre']) ?> &rarr; <?= esc($pago['receptor_nombre']) ?>
                    </div>
                    <div class="text-muted small mobile-transaction-meta">Grupo: <?= esc($pago['grupo_nombre']) ?></div>
                </div>
                <div class="mobile-transaction-side">
                    <div class="fw-bold fs-5 text-success mobile-transaction-amount">$<?= number_format($pago['monto'], 2) ?></div>
                </div>
            </div>
        </div>
    </a>
<?php endforeach; ?>
