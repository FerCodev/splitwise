<?php
$monto = (float) ($monto ?? 0);
$acreedorNombre = $acreedorNombre ?? '';
$acreedorId = (int) ($acreedorId ?? 0);
$grupoId = (int) ($grupoId ?? 0);
$grupoEstado = $grupoEstado ?? 'activo';
$mediosCobro = $mediosCobro ?? [];
$formId = $formId ?? ('deuda-' . $acreedorId);
$fechaDefault = $fechaDefault ?? date('Y-m-d');
$esCerrado = $grupoEstado === 'cerrado';
$esLiquidado = $grupoEstado === 'liquidado';

$ctaLabel = $esCerrado ? 'Saldar deuda' : 'Registrar pago';
$ctaClass = $esCerrado ? 'btn-warning' : 'btn-success';
$formAction = $esCerrado
    ? base_url('grupos/' . $grupoId . '/saldar-deuda')
    : base_url('pagos');
$contextoTexto = $esCerrado
    ? 'Este grupo está cerrado. Saldá esta deuda para poder liquidarlo.'
    : 'Registrá un pago para actualizar el balance.';
?>
<div class="deuda-pendiente-card">
    <div class="deuda-pendiente-header">
        <span class="deuda-pendiente-pill">Le debés</span>
        <strong class="deuda-pendiente-acreedor">a <?= esc($acreedorNombre) ?></strong>
    </div>
    <div class="deuda-pendiente-monto"><?= moneda($monto) ?></div>
    <p class="deuda-pendiente-contexto"><?= esc($contextoTexto) ?></p>

    <?php if (!$esLiquidado): ?>
    <button type="button"
        class="btn <?= $ctaClass ?> deuda-pendiente-cta"
        data-target="<?= esc($formId, 'attr') ?>"
        aria-expanded="false"
        aria-controls="<?= esc($formId, 'attr') ?>">
        <?= esc($ctaLabel) ?>
    </button>

    <div id="<?= esc($formId, 'attr') ?>" class="deuda-pendiente-form d-none">
        <form action="<?= esc($formAction, 'attr') ?>" method="post">
            <?= csrf_field() ?>

            <?php if (!$esCerrado): ?>
            <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
            <input type="hidden" name="origen" value="grupo_balance_detalle">
            <?php endif; ?>

            <input type="hidden" name="receptor_id" value="<?= $acreedorId ?>">

            <div class="mb-2">
                <label class="form-label small mb-1">Monto (máx. <?= moneda($monto) ?>)</label>
                <input type="number" step="0.01" min="0.01" max="<?= esc(number_format($monto, 2, '.', '')) ?>"
                    name="monto" class="form-control"
                    value="<?= esc(number_format($monto, 2, '.', '')) ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= esc($fechaDefault) ?>" required>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Descripción</label>
                <input type="text" name="descripcion" class="form-control"
                    value="<?= $esCerrado ? esc('Saldar deuda - ' . $acreedorNombre) : esc('Pago de deuda - ' . $acreedorNombre) ?>"
                    maxlength="255">
            </div>
            <button type="submit" class="btn <?= $ctaClass ?> w-100">Confirmar pago</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (!$esLiquidado && !empty($mediosCobro)): ?>
    <div class="deuda-pendiente-medios">
        <p class="small text-muted fw-medium mb-1">Medios de cobro de <?= esc($acreedorNombre) ?>:</p>
        <?php foreach ($mediosCobro as $m): ?>
        <div class="deuda-pendiente-medio">
            <span class="small fw-medium"><?= esc($m['nombre'] ?? $m['tipo']) ?></span>
            <div class="d-flex flex-wrap gap-1 mt-1">
                <?php if (!empty($m['alias'])): ?>
                <span class="small text-muted me-2">Alias: <?= esc($m['alias']) ?></span>
                <button type="button" class="btn btn-sm btn-secondary copiar-btn"
                    data-copiar="<?= esc($m['alias'], 'attr') ?>" style="min-height:44px">Copiar alias</button>
                <?php endif; ?>
                <?php if (!empty($m['cbu_cvu'])): ?>
                <span class="small text-muted me-2">CBU/CVU: <?= esc($m['cbu_cvu']) ?></span>
                <button type="button" class="btn btn-sm btn-secondary copiar-btn"
                    data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>" style="min-height:44px">Copiar CBU/CVU</button>
                <?php endif; ?>
                <?php if (!empty($m['payment_link'])): ?>
                <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer"
                    class="btn btn-sm btn-primary" style="min-height:44px">Abrir link de pago</a>
                <?php endif; ?>
                <?php if (!empty($m['banco'])): ?>
                <span class="small text-muted">- <?= esc($m['banco']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php elseif (!$esLiquidado): ?>
    <p class="deuda-pendiente-sin-medios small text-muted mt-2 mb-0"><?= esc($acreedorNombre) ?> no tiene medios de cobro registrados.</p>
    <?php endif; ?>
</div>
