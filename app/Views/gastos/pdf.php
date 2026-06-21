<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gastos</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #666; font-size: 10px; margin-bottom: 14px; }
        .summary { margin-bottom: 14px; padding: 10px; background: #f3f6fb; border-radius: 4px; }
        .summary strong { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eef2f7; text-align: left; padding: 6px; border-bottom: 1px solid #d9dee7; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Gastos</h1>
    <div class="meta">Generado el <?= esc($fecha) ?></div>
    <div class="summary">
        <strong>Total: <?= moneda($total) ?></strong><br>
        <span class="muted"><?= count($gastos) ?> gasto(s) exportado(s)</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripcion</th>
                <th>Grupo</th>
                <th>Categoria</th>
                <th>Pago</th>
                <th class="num">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gastos as $gasto): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                    <td><?= esc($gasto['descripcion']) ?></td>
                    <td><?= esc($gasto['grupo_nombre']) ?></td>
                    <td><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></td>
                    <td><?= esc($gasto['pagador_nombre']) ?></td>
                    <td class="num"><?= moneda($gasto['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
