<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background: #e9eef7; font-weight: bold; }
        .number { mso-number-format:"\#\.\#\#0\,00"; }
        .total { font-weight: bold; background: #f0fdf4; }
    </style>
</head>
<body>
    <table>
        <tr><th colspan="6">Pagos exportados</th></tr>
        <tr><td colspan="6">Generado el <?= esc($fecha) ?></td></tr>
        <tr><th>Fecha</th><th>Descripcion</th><th>Grupo</th><th>Pago</th><th>Recibio</th><th>Monto</th></tr>
        <?php foreach ($pagos as $pago): ?>
            <tr>
                <td><?= esc($pago['fecha']) ?></td>
                <td><?= esc($pago['descripcion'] ?: 'Pago') ?></td>
                <td><?= esc($pago['grupo_nombre']) ?></td>
                <td><?= esc($pago['pagador_nombre']) ?></td>
                <td><?= esc($pago['receptor_nombre']) ?></td>
                <td class="number"><?= numero_arg((float) $pago['monto']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total"><td colspan="5">Total filtrado</td><td class="number"><?= numero_arg($total) ?></td></tr>
    </table>
</body>
</html>
