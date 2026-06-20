<?php foreach ($gastos as $gasto): ?>
    <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-link" style="border-left: 3px solid #2563eb;">
        <div class="card-body mobile-transaction-body">
            <div class="mobile-transaction-main">
                <div class="mobile-transaction-info">
                    <div class="fw-medium mobile-transaction-title"><?= esc($gasto['descripcion']) ?></div>
                    <div class="text-muted small mt-1">
                        <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                        <?= esc($gasto['pagador_nombre']) ?>
                    </div>
                    <div class="text-muted small mobile-transaction-meta">
                        <span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                        Grupo: <?= esc($gasto['grupo_nombre']) ?> &middot; <?= $gasto['total_participantes'] ?> part.
                    </div>
                </div>
                <div class="mobile-transaction-side">
                    <div class="fw-bold fs-5 text-primary mobile-transaction-amount">$<?= number_format($gasto['monto'], 2) ?></div>
                </div>
            </div>
        </div>
    </a>
<?php endforeach; ?>
