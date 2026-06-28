<?= view('partials/_head', ['title' => 'Gastito - Reportes']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Reportes']) ?>
<?php
    $mesReporte = $resumenMensual['mes'] ?? ($filters['year_month'] ?? date('Y-m'));
    $mesReporteTexto = $mesReporte ? date('m/Y', strtotime($mesReporte . '-01')) : 'periodo';
    $csvUrl = base_url('reportes/exportar?' . http_build_query($filters));
    $pdfUrl = base_url('reportes/exportar-pdf?' . http_build_query($filters));
    $saldo = (float) ($resumenMensual['saldo'] ?? 0);
    $hasFilters = !empty($filters['grupo_id']) || !empty($filters['categoria_id']) || !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']);
?>

<div class="container mt-3 mt-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-1 d-none d-md-block">Reportes</h2>
            <div class="text-muted small">Periodo: <?= esc($mesReporteTexto) ?><?= $hasFilters ? ' con filtros' : '' ?></div>
        </div>
        <div class="d-flex gap-1">
            <a href="<?= $pdfUrl ?>" class="btn btn-danger btn-sm">PDF</a>
            <a href="<?= $csvUrl ?>" class="btn btn-success btn-sm">CSV</a>
        </div>
    </div>

    <form method="get" class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2"><label class="form-label small fw-medium">Mes</label><input type="month" name="year_month" class="form-control" value="<?= esc($filters['year_month'] ?? $mesReporte) ?>"></div>
                <div class="col-6 col-md-3"><label class="form-label small fw-medium">Grupo</label><select name="grupo_id" class="form-select"><option value="">Todos</option><?php foreach ($grupos as $g): ?><option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option><?php endforeach; ?></select></div>

                <div class="col-6 col-md-2"><label class="form-label small fw-medium">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>"></div>
                <div class="col-6 col-md-2"><label class="form-label small fw-medium">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>"></div>                <div class="col-12 col-md-3"><label class="form-label small fw-medium">Categoria</label><select name="categoria_id" class="form-select"><option value="">Todas</option><?php foreach ($categorias as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-12 d-flex gap-2 justify-content-end mt-3"><a href="<?= base_url('reportes') ?>" class="btn btn-outline-secondary btn-sm">Limpiar</a><button type="submit" class="btn btn-primary btn-sm">Aplicar</button></div>
            </div>
        </div>
    </form>

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Total gastado</div><div class="fw-bold fs-5 text-primary"><?= moneda($resumenMensual['total_gastado']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Vos pagaste</div><div class="fw-bold fs-5 text-success"><?= moneda($resumenMensual['total_pagado']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Tu parte</div><div class="fw-bold fs-5 text-secondary"><?= moneda($resumenMensual['total_consumido']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Saldo del periodo</div><div class="fw-bold fs-5 <?= $saldo >= 0 ? 'text-success' : 'text-danger' ?>"><?= moneda(abs($saldo)) ?> <span class="small fw-normal"><?= $saldo > 0 ? 'a favor' : ($saldo < 0 ? 'debe' : 'saldado') ?></span></div></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center"><h5 class="mb-0 fw-bold">Gastitos por grupo</h5><span class="text-muted small"><?= (int) ($resumenMensual['grupos_activos'] ?? 0) ?> activo(s)</span></div><div class="card-body p-0"><?php if (empty($porGrupo)): ?><p class="text-muted small p-3 mb-0">Sin gastitos para el periodo.</p><?php else: ?><?php $maxGrupo = max(array_map(static fn($g) => (float) $g['total'], $porGrupo)); ?><?php foreach ($porGrupo as $g): ?><div class="mobile-card-item"><div class="d-flex justify-content-between align-items-center gap-2"><div class="fw-medium"><?= esc($g['grupo']) ?> <span class="text-muted small"><?= $g['cantidad'] ?> <?= pluralizar((int) $g['cantidad'], 'gastito', 'gastitos') ?></span></div><div class="fw-bold text-primary"><?= moneda($g['total']) ?></div></div><div class="progress mt-2" style="height:4px"><div class="progress-bar bg-primary" style="width:<?= $maxGrupo > 0 ? ($g['total'] / $maxGrupo * 100) : 0 ?>%"></div></div></div><?php endforeach; ?><?php endif; ?></div></div></div>
        <div class="col-12 col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Gastitos por categoría</h5></div><div class="card-body p-0"><?php if (empty($porCategoria)): ?><p class="text-muted small p-3 mb-0">Sin categorias para el periodo.</p><?php else: ?><?php $maxCategoria = max(array_map(static fn($c) => (float) $c['total'], $porCategoria)); ?><?php foreach ($porCategoria as $c): ?><div class="mobile-card-item"><div class="d-flex justify-content-between align-items-center gap-2"><div><span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span> <span class="text-muted small"><?= $c['cantidad'] ?> <?= pluralizar((int) $c['cantidad'], 'gastito', 'gastitos') ?></span></div><div class="fw-bold"><?= moneda($c['total']) ?></div></div><div class="progress mt-2" style="height:4px"><div class="progress-bar bg-warning" style="width:<?= $maxCategoria > 0 ? ($c['total'] / $maxCategoria * 100) : 0 ?>%"></div></div></div><?php endforeach; ?><?php endif; ?></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Movimientos del periodo</h5></div><div class="card-body report-movement-list"><?php if (empty($movimientos)): ?><p class="text-muted small p-3 mb-0">No hay movimientos para el periodo.</p><?php else: ?><?php foreach ($movimientos as $m): ?><?= view('components/cards/movimiento', ['tipo' => $m['tipo'], 'descripcion' => $m['descripcion'], 'monto' => $m['monto'], 'fecha' => $m['fecha'], 'persona' => $m['persona'], 'grupo' => $m['grupo'] ?? null, 'url' => base_url(($m['tipo'] === 'gasto' ? 'gastos/' : 'pagos/') . $m['id'])]) ?><?php endforeach; ?><?php endif; ?></div></div></div>
        <div class="col-12 col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Saldos actuales</h5></div><div class="card-body report-debt-list"><?php if (empty($deudas)): ?><p class="text-muted small mb-0">No hay deudas pendientes.</p><?php else: ?><?php foreach ($deudas as $d): ?><div class="report-debt-item"><div class="d-flex justify-content-between align-items-center gap-2"><div><strong><?= esc($d['deudor']) ?></strong><span class="text-muted"> le debe a </span><strong><?= esc($d['acreedor']) ?></strong><?php if (!empty($d['grupo'])): ?><div class="text-muted small"><?= esc($d['grupo']) ?></div><?php endif; ?></div><div class="fw-bold text-danger"><?= moneda($d['monto']) ?></div></div></div><?php endforeach; ?><?php endif; ?></div></div></div>
    </div>
</div>

<?= view('partials/_footer') ?>
