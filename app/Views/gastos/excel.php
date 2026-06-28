<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; }
        th { background: #e9eef7; font-weight: bold; }
        .number { mso-number-format:"\#\.\#\#0\,00"; }
        .total { font-weight: bold; background: #f3f6fb; }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="7">Gastitos exportados</th>
        </tr>
        <tr>
            <td colspan="7">Generado el <?= esc($fecha) ?></td>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Descripcion</th>
            <th>Grupo</th>
            <th>Categoria</th>
            <th>Pago</th>
            <th>Participantes</th>
            <th>Monto</th>
        </tr>
        <?php foreach ($gastos as $gasto): ?>
            <tr>
                <td><?= esc($gasto['fecha']) ?></td>
                <td><?= esc($gasto['descripcion']) ?></td>
                <td><?= esc($gasto['grupo_nombre']) ?></td>
                <td><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></td>
                <td><?= esc($gasto['pagador_nombre']) ?></td>
                <td><?= esc($gasto['total_participantes']) ?></td>
                <td class="number"><?= numero_arg((float) $gasto['monto']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td colspan="6">Total filtrado</td>
            <td class="number"><?= numero_arg($total) ?></td>
        </tr>
    </table>
</body>
</html>
