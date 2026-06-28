<?php
use App\Services\UserColor;

$colorMap = $colorMap ?? [];
?>
<?php foreach ($gastos as $gasto): ?>
    <?php
        $colorKey = $colorMap[(int) $gasto['id']] ?? UserColor::DEFAULT_KEY;
    ?>
    <?= view('components/cards/movimiento', [
        'tipo' => 'gasto',
        'descripcion' => $gasto['descripcion'],
        'monto' => $gasto['monto'],
        'fecha' => $gasto['fecha'],
        'persona' => $gasto['pagador_nombre'],
        'categoria' => $gasto['categoria_nombre'] ?? null,
        'grupo' => $gasto['grupo_nombre'],
        'participantes' => $gasto['total_participantes'] ?? null,
        'colorKey' => $colorKey,
        'url' => base_url('gastos/' . $gasto['id']),
    ]) ?>
<?php endforeach; ?>
