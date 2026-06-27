<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Gastito</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .fecha { color: #888; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f0f0f0; text-align: left; padding: 6px 8px; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        .resumen td { font-size: 13px; font-weight: bold; }
        .verde { color: #198754; }
        .rojo { color: #dc3545; }
        .total { font-weight: bold; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>Reporte de gastos</h1>
    <div class="fecha">Generado el <?= $fecha ?></div>

    <table class="resumen">
        <tr><td>Total gastado este mes</td><td class="total"><?= moneda($resumenMensual['total_gastado']) ?></td></tr>
        <tr><td>Total pagado este mes</td><td class="total verde"><?= moneda($resumenMensual['total_pagado']) ?></td></tr>
        <tr><td>Saldo del mes</td><td class="total <?= $resumenMensual['saldo'] >= 0 ? 'verde' : 'rojo' ?>"><?= moneda(abs($resumenMensual['saldo'])) ?></td></tr>
        <tr><td>Grupos con actividad</td><td class="total"><?= $resumenMensual['grupos_activos'] ?></td></tr>
    </table>

    <?php if (!empty($topGrupos)): ?>
    <div class="section-title">Top grupos por gasto</div>
    <table>
        <tr><th>Grupo</th><th>Gastos</th><th>Total</th></tr>
        <?php foreach ($topGrupos as $g): ?>
        <tr><td><?= htmlspecialchars($g['nombre']) ?></td><td><?= $g['cantidad'] ?></td><td><?= moneda($g['total']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if (!empty($topCategorias)): ?>
    <div class="section-title">Top categor&iacute;as</div>
    <table>
        <tr><th>Categor&iacute;a</th><th>Cantidad</th><th>Total</th></tr>
        <?php foreach ($topCategorias as $c): ?>
        <tr><td><?= htmlspecialchars($c['categoria']) ?></td><td><?= $c['cantidad'] ?></td><td><?= moneda($c['total']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if (!empty($movimientos)): ?>
    <div class="section-title">&Uacute;ltimos movimientos</div>
    <table>
        <tr><th>Fecha</th><th>Tipo</th><th>Descripci&oacute;n</th><th>Monto</th></tr>
        <?php foreach ($movimientos as $m): ?>
        <tr><td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td><td><?= $m['tipo'] ?></td><td><?= htmlspecialchars($m['descripcion']) ?></td><td><?= moneda($m['monto']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if (!empty($deudas)): ?>
    <div class="section-title">Pagos pendientes</div>
    <table>
        <tr><th>Deudor</th><th>Acreedor</th><th>Monto</th></tr>
        <?php foreach ($deudas as $d): ?>
        <tr><td><?= htmlspecialchars($d['deudor']) ?></td><td><?= htmlspecialchars($d['acreedor']) ?></td><td class="rojo"><?= moneda($d['monto']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</body>
</html>
