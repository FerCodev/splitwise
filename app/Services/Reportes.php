<?php

namespace App\Services;

class Reportes
{
    public static function resumenGlobal(int $userId): array
    {
        $db = \Config\Database::connect();
        $gruposDelUser = self::gruposIds($userId);
        if (empty($gruposDelUser)) {
            return self::calcularResumen(0, 0, 0, 0);
        }
        $ids = implode(',', $gruposDelUser);
        $totalGastado = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE grupo_id IN ({$ids})"
        )->getRow());
        $totalPagadoGastos = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE pagador_id = ? AND grupo_id IN ({$ids})",
            [$userId]
        )->getRow());
        $totalConsumido = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(gp.monto_asignado), 0) AS total FROM gasto_participantes gp JOIN gastos g ON g.id = gp.gasto_id WHERE gp.user_id = ? AND g.grupo_id IN ({$ids})",
            [$userId]
        )->getRow());
        $totalPagosEnviados = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM pagos WHERE pagador_id = ? AND grupo_id IN ({$ids})",
            [$userId]
        )->getRow());
        $totalRecibido = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM pagos WHERE receptor_id = ? AND grupo_id IN ({$ids})",
            [$userId]
        )->getRow());
        return self::calcularResumen($totalGastado, $totalPagadoGastos, $totalRecibido, $totalConsumido, $totalPagosEnviados);
    }

    public static function resumenMensual(int $userId, string $yearMonth = ''): array
    {
        $db = \Config\Database::connect();
        $gruposDelUser = self::gruposIds($userId);
        if (empty($gruposDelUser)) {
            return self::resumenVacio();
        }
        if (empty($yearMonth)) {
            $yearMonth = date('Y-m');
            $tieneDatos = self::tieneGastosEnMes($gruposDelUser, $yearMonth);
            if (!$tieneDatos) {
                $ultimoMes = self::ultimoMesConActividad($gruposDelUser);
                if ($ultimoMes) {
                    $yearMonth = $ultimoMes;
                }
            }
        }
        $ids = implode(',', $gruposDelUser);
        $totalGastado = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE grupo_id IN ({$ids}) AND DATE_FORMAT(fecha, '%Y-%m') = ?",
            [$yearMonth]
        )->getRow());
        $totalPagadoGastos = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE pagador_id = ? AND grupo_id IN ({$ids}) AND DATE_FORMAT(fecha, '%Y-%m') = ?",
            [$userId, $yearMonth]
        )->getRow());
        $totalConsumido = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(gp.monto_asignado), 0) AS total FROM gasto_participantes gp JOIN gastos g ON g.id = gp.gasto_id WHERE gp.user_id = ? AND g.grupo_id IN ({$ids}) AND DATE_FORMAT(g.fecha, '%Y-%m') = ?",
            [$userId, $yearMonth]
        )->getRow());
        $totalPagos = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM pagos WHERE grupo_id IN ({$ids}) AND DATE_FORMAT(fecha, '%Y-%m') = ?",
            [$yearMonth]
        )->getRow());
        $gruposActivos = self::scalarTotal($db->query(
            "SELECT COUNT(DISTINCT g.grupo_id) AS total FROM gastos g WHERE g.grupo_id IN ({$ids}) AND DATE_FORMAT(g.fecha, '%Y-%m') = ?",
            [$yearMonth]
        )->getRow());
        return [
            'mes' => $yearMonth,
            'total_gastado' => round($totalGastado, 2),
            'total_pagado' => round($totalPagadoGastos, 2),
            'total_consumido' => round($totalConsumido, 2),
            'total_pagos' => round($totalPagos, 2),
            'saldo' => round($totalPagadoGastos - $totalConsumido, 2),
            'grupos_activos' => (int) $gruposActivos,
        ];
    }

    public static function topGrupos(int $userId, string $yearMonth = '', int $limit = 5): array
    {
        $db = \Config\Database::connect();
        $gruposDelUser = self::gruposIds($userId);
        if (empty($gruposDelUser)) return [];
        if (empty($yearMonth)) $yearMonth = date('Y-m');
        $ids = implode(',', $gruposDelUser);
        return $db->query(
            "SELECT gr.nombre, gr.id, COALESCE(SUM(g.monto), 0) AS total, COUNT(g.id) AS cantidad
               FROM grupos gr
               LEFT JOIN gastos g ON g.grupo_id = gr.id AND DATE_FORMAT(g.fecha, '%Y-%m') = ?
               WHERE gr.id IN ({$ids})
               GROUP BY gr.id, gr.nombre
               ORDER BY total DESC LIMIT ?",
            [$yearMonth, $limit]
        )->getResultArray();
    }

    public static function topCategorias(int $userId, string $yearMonth = '', int $limit = 10): array
    {
        $db = \Config\Database::connect();
        $gruposDelUser = self::gruposIds($userId);
        if (empty($gruposDelUser)) return [];
        if (empty($yearMonth)) $yearMonth = date('Y-m');
        $ids = implode(',', $gruposDelUser);
        return $db->query(
            "SELECT COALESCE(c.nombre, 'Otros') AS categoria, COUNT(g.id) AS cantidad, SUM(g.monto) AS total
               FROM gastos g
               LEFT JOIN categorias c ON c.id = g.categoria_id
               WHERE g.grupo_id IN ({$ids}) AND DATE_FORMAT(g.fecha, '%Y-%m') = ?
               GROUP BY g.categoria_id, c.nombre
               ORDER BY total DESC LIMIT ?",
            [$yearMonth, $limit]
        )->getResultArray();
    }

    public static function gastosPorCategoria(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');
        $sql = "SELECT COALESCE(c.nombre, 'Otros') AS categoria, COUNT(g.id) AS cantidad, SUM(g.monto) AS total
                  FROM gastos g
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                  LEFT JOIN categorias c ON c.id = g.categoria_id
                 WHERE {$where['sql']}
                 GROUP BY g.categoria_id, c.nombre ORDER BY total DESC";
        return $db->query($sql, $where['binds'])->getResultArray();
    }

    public static function gastosPorGrupo(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');
        $sql = "SELECT gr.nombre AS grupo, COUNT(g.id) AS cantidad, SUM(g.monto) AS total
                  FROM gastos g
                  JOIN grupos gr ON gr.id = g.grupo_id
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                 WHERE {$where['sql']}
                 GROUP BY g.grupo_id, gr.nombre ORDER BY total DESC";
        return $db->query($sql, $where['binds'])->getResultArray();
    }

    public static function ultimosMovimientos(int $userId, int $limit = 10): array
    {
        $db = \Config\Database::connect();
        $gruposIds = self::gruposIds($userId);
        if (empty($gruposIds)) return [];
        $ids = implode(',', $gruposIds);
        $gastos = $db->query(
            "SELECT g.id, 'gasto' AS tipo, g.descripcion, g.monto, g.fecha, u.name AS persona, gr.nombre AS grupo
               FROM gastos g JOIN users u ON u.id = g.pagador_id JOIN grupos gr ON gr.id = g.grupo_id
               WHERE g.grupo_id IN ({$ids}) ORDER BY g.fecha DESC, g.created_at DESC LIMIT ?",
            [$limit]
        )->getResultArray();
        $pagos = $db->query(
            "SELECT p.id, 'pago' AS tipo, COALESCE(p.descripcion, 'Pago') AS descripcion, p.monto, p.fecha,
                    CONCAT(pag.name, ' pagó a ', rec.name) AS persona, gr.nombre AS grupo
               FROM pagos p
               JOIN users pag ON pag.id = p.pagador_id
               JOIN users rec ON rec.id = p.receptor_id
               JOIN grupos gr ON gr.id = p.grupo_id
              WHERE p.grupo_id IN ({$ids})
              ORDER BY p.fecha DESC, p.created_at DESC LIMIT ?",
            [$limit]
        )->getResultArray();
        $movimientos = array_merge($gastos, $pagos);
        usort($movimientos, static fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
        return array_slice($movimientos, 0, $limit);
    }

    public static function ultimosMovimientosPorGrupo(int $grupoId, int $limit = 10): array
    {
        $db = \Config\Database::connect();
        $gastos = $db->query(
            "SELECT g.id, 'gasto' AS tipo, g.descripcion, g.monto, g.fecha, u.name AS persona
               FROM gastos g JOIN users u ON u.id = g.pagador_id
               WHERE g.grupo_id = ? ORDER BY g.fecha DESC, g.created_at DESC LIMIT ?",
            [$grupoId, $limit]
        )->getResultArray();
        $pagos = $db->query(
            "SELECT p.id, 'pago' AS tipo, COALESCE(p.descripcion, 'Pago') AS descripcion, p.monto, p.fecha,
                    CONCAT(pag.name, ' pagó a ', rec.name) AS persona
               FROM pagos p JOIN users pag ON pag.id = p.pagador_id JOIN users rec ON rec.id = p.receptor_id
               WHERE p.grupo_id = ? ORDER BY p.fecha DESC, p.created_at DESC LIMIT ?",
            [$grupoId, $limit]
        )->getResultArray();
        $movimientos = array_merge($gastos, $pagos);
        usort($movimientos, static fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
        return array_slice($movimientos, 0, $limit);
    }

    public static function evolucionMensualGrupo(int $grupoId, int $meses = 6): array
    {
        $db = \Config\Database::connect();
        return $db->query(
            "SELECT DATE_FORMAT(g.fecha, '%Y-%m') AS mes, COUNT(g.id) AS cantidad, SUM(g.monto) AS total
               FROM gastos g WHERE g.grupo_id = ? AND g.fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
               GROUP BY DATE_FORMAT(g.fecha, '%Y-%m') ORDER BY mes ASC",
            [$grupoId, $meses]
        )->getResultArray();
    }

    public static function resumenGrupo(int $grupoId): array
    {
        $db = \Config\Database::connect();
        $totalGastado = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos WHERE grupo_id = ?", [$grupoId]
        )->getRow());
        $totalPagado = self::scalarTotal($db->query(
            "SELECT COALESCE(SUM(monto), 0) AS total FROM pagos WHERE grupo_id = ?", [$grupoId]
        )->getRow());
        $cantGastos = (int) $db->query(
            "SELECT COUNT(*) AS c FROM gastos WHERE grupo_id = ?", [$grupoId]
        )->getRow()->c;
        $cantPagos = (int) $db->query(
            "SELECT COUNT(*) AS c FROM pagos WHERE grupo_id = ?", [$grupoId]
        )->getRow()->c;
        return [
            'total_gastado' => round($totalGastado, 2),
            'total_pagado' => round($totalPagado, 2),
            'cant_gastos' => $cantGastos,
            'cant_pagos' => $cantPagos,
        ];
    }

    public static function gastosParaExportar(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');
        $sql = "SELECT g.fecha, gr.nombre AS grupo, COALESCE(c.nombre, 'Otros') AS categoria,
                       g.descripcion, u.name AS pagador, g.monto
                  FROM gastos g
                  JOIN grupos gr ON gr.id = g.grupo_id
                  JOIN users u ON u.id = g.pagador_id
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                  LEFT JOIN categorias c ON c.id = g.categoria_id
                 WHERE {$where['sql']} ORDER BY g.fecha DESC, g.created_at DESC";
        return $db->query($sql, $where['binds'])->getResultArray();
    }

    public static function calcularResumen(float $totalGastado, float $totalPagado, float $totalRecibido, float $totalConsumido, float $totalPagosEnviados = 0.0): array
    {
        return [
            'total_gastado' => round($totalGastado, 2),
            'total_pagado' => round($totalPagado, 2),
            'total_recibido' => round($totalRecibido, 2),
            'total_consumido' => round($totalConsumido, 2),
            'total_pagos_enviados' => round($totalPagosEnviados, 2),
            'saldo' => round($totalPagado - $totalConsumido - $totalRecibido + $totalPagosEnviados, 2),
        ];
    }

    public static function formatearFilasCsv(array $rows): array
    {
        $lineas = [];
        foreach ($rows as $r) {
            $lineas[] = [
                $r['fecha'] ?? '', $r['grupo'] ?? '', $r['categoria'] ?? '',
                $r['descripcion'] ?? '', $r['pagador'] ?? '',
                number_format((float) ($r['monto'] ?? 0), 2, '.', ''),
            ];
        }
        return $lineas;
    }

    private static function gruposIds(int $userId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->query('SELECT grupo_id FROM grupo_miembros WHERE user_id = ?', [$userId])->getResultArray();
        return array_map('intval', array_column($rows, 'grupo_id'));
    }

    private static function buildWhere(int $userId, array $filters, string $alias): array
    {
        $binds = [$userId];
        $clauses = [];
        if (!empty($filters['grupo_id'])) { $clauses[] = "{$alias}.grupo_id = ?"; $binds[] = (int) $filters['grupo_id']; }
        if (!empty($filters['categoria_id'])) { $clauses[] = "{$alias}.categoria_id = ?"; $binds[] = (int) $filters['categoria_id']; }
        if (!empty($filters['fecha_desde'])) { $clauses[] = "{$alias}.fecha >= ?"; $binds[] = $filters['fecha_desde']; }
        if (!empty($filters['fecha_hasta'])) { $clauses[] = "{$alias}.fecha <= ?"; $binds[] = $filters['fecha_hasta']; }
        return ['sql' => $clauses ? implode(' AND ', $clauses) : '1=1', 'binds' => $binds];
    }

    private static function scalarTotal(?object $row): float { return round((float) ($row->total ?? 0), 2); }

    private static function ordenarAgrupadoPorTotal(array $agrupado): array
    {
        $rows = array_values(array_map(static fn(array $row): array => ['total' => round((float) $row['total'], 2)] + $row, $agrupado));
        usort($rows, static fn(array $a, array $b): int => $b['total'] <=> $a['total'] ?: strcmp((string) ($a['categoria'] ?? $a['grupo'] ?? ''), (string) ($b['categoria'] ?? $b['grupo'] ?? '')));
        return $rows;
    }

    private static function tieneGastosEnMes(array $gruposIds, string $yearMonth): bool
    {
        $db = \Config\Database::connect();
        if (empty($gruposIds)) return false;
        $ids = implode(',', $gruposIds);
        $row = $db->query("SELECT COUNT(*) AS c FROM gastos WHERE grupo_id IN ({$ids}) AND DATE_FORMAT(fecha, '%Y-%m') = ?", [$yearMonth])->getRow();
        return $row && (int) $row->c > 0;
    }

    private static function ultimoMesConActividad(array $gruposIds): ?string
    {
        $db = \Config\Database::connect();
        if (empty($gruposIds)) return null;
        $ids = implode(',', $gruposIds);
        $row = $db->query("SELECT DATE_FORMAT(MAX(fecha), '%Y-%m') AS mes FROM gastos WHERE grupo_id IN ({$ids})")->getRow();
        return ($row && $row->mes) ? $row->mes : null;
    }

    private static function resumenVacio(): array
    {
        return ['mes' => date('Y-m'), 'total_gastado' => 0, 'total_pagado' => 0, 'total_consumido' => 0, 'total_pagos' => 0, 'saldo' => 0, 'grupos_activos' => 0];
    }

    public static function deudasPendientes(int $userId, int $limit = 5): array
    {
        $gruposIds = self::gruposIds($userId);
        if (empty($gruposIds)) return [];
        $gastoModel = new \App\Models\Gasto();
        $todas = [];
        foreach ($gruposIds as $gid) {
            $balance = $gastoModel->getBalanceByGrupo($gid);
            $deudas = \App\Models\Gasto::computeDeudasFromBalance($balance);
            $deudasUsuario = array_values(array_filter($deudas, fn($d) => (int) $d['deudor_id'] === $userId || (int) $d['acreedor_id'] === $userId));
            $todas = array_merge($todas, $deudasUsuario);
        }
        usort($todas, fn($a, $b) => $b['monto'] <=> $a['monto']);
        return array_slice($todas, 0, $limit);
    }
}
