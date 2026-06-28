<?php
use App\Services\UserColor;

$colorMap = $colorMap ?? [];
$systemBg = UserColor::RESERVED['system']['bg'];
$systemBorder = UserColor::RESERVED['system']['border'];
$systemSolid = UserColor::RESERVED['system']['solid'];
?>
<?php foreach ($gastos as $gasto): ?>
    <?php
        $colorKey = $colorMap[(int) $gasto['id']] ?? UserColor::DEFAULT_KEY;
        $colorInfo = UserColor::get($colorKey);
        $bg = $colorInfo['bg'] ?? $systemBg;
        $border = $colorInfo['border'] ?? $systemBorder;
        $solid = $colorInfo['solid'] ?? $systemSolid;
    ?>
    <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="report-movement-link">
        <div class="report-movement-card report-movement-expense" style="border-left: 3px solid <?= esc($border) ?>; background: <?= esc($bg) ?>;">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="min-width-0">
                    <span class="badge me-1" style="background: <?= esc($solid) ?>; color: #fff;">Gastito</span>
                    <span class="fw-medium small"><?= esc($gasto['descripcion']) ?></span>
                </div>
                <span class="fw-bold small text-nowrap" style="color: <?= esc($solid) ?>;"><?= moneda($gasto['monto']) ?></span>
            </div>
            <div class="text-muted small mt-1">
                <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                <?= esc($gasto['pagador_nombre']) ?>
            </div>
            <div class="text-muted small mt-1">
                <span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                Grupo: <?= esc($gasto['grupo_nombre']) ?> &middot; <?= $gasto['total_participantes'] ?> part.
            </div>
        </div>
    </a>
<?php endforeach; ?>
