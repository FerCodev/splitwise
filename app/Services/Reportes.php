<?php

namespace App\Services;

class Reportes
{
    /**
     * Retorna resumen global del usuario: totales y saldo.
     */
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
            "SELECT COALESCE(SUM(gp.monto_asignado), 0) AS total
               FROM gasto_participantes gp
               JOIN gastos g ON g.id = gp.gasto_id
              WHERE gp.user_id = ? AND g.grupo_id IN ({$ids})",
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

    /**
     * Gastos por categoria, filtrados y limitados al usuario.
     */
    public static function gastosPorCategoria(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');

        $sql = "SELECT COALESCE(c.nombre, 'Otros') AS categoria,
                       COUNT(g.id) AS cantidad,
                       SUM(g.monto) AS total
                  FROM gastos g
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                  LEFT JOIN categorias c ON c.id = g.categoria_id
                 WHERE {$where['sql']}
                 GROUP BY g.categoria_id, c.nombre
                 ORDER BY total DESC";

        return $db->query($sql, $where['binds'])->getResultArray();
    }

    /**
     * Gastos por grupo, filtrados y limitados al usuario.
     */
    public static function gastosPorGrupo(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');

        $sql = "SELECT gr.nombre AS grupo,
                       COUNT(g.id) AS cantidad,
                       SUM(g.monto) AS total
                  FROM gastos g
                  JOIN grupos gr ON gr.id = g.grupo_id
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                 WHERE {$where['sql']}
                 GROUP BY g.grupo_id, gr.nombre
                 ORDER BY total DESC";

        return $db->query($sql, $where['binds'])->getResultArray();
    }

    /**
     * Ultimos movimientos combinados (gastos + pagos).
     */
    public static function ultimosMovimientos(int $userId, int $limit = 10): array
    {
        $db = \Config\Database::connect();
        $gruposIds = self::gruposIds($userId);
        if (empty($gruposIds)) {
            return [];
        }

        $ids = implode(',', $gruposIds);

        $gastos = $db->query(
            "SELECT g.id, 'gasto' AS tipo, g.descripcion, g.monto, g.fecha,
                    u.name AS persona, gr.nombre AS grupo
               FROM gastos g
               JOIN users u ON u.id = g.pagador_id
               JOIN grupos gr ON gr.id = g.grupo_id
              WHERE g.grupo_id IN ({$ids})
              ORDER BY g.fecha DESC, g.created_at DESC
              LIMIT ?",
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
              ORDER BY p.fecha DESC, p.created_at DESC
              LIMIT ?",
            [$limit]
        )->getResultArray();

        $movimientos = array_merge($gastos, $pagos);
        usort($movimientos, static fn(array $a, array $b): int => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));

        return array_slice($movimientos, 0, $limit);
    }

    /**
     * Gastos filtrados para exportacion CSV.
     */
    public static function gastosParaExportar(int $userId, array $filters = []): array
    {
        $db = \Config\Database::connect();
        $where = self::buildWhere($userId, $filters, 'g');

        $sql = "SELECT g.fecha, gr.nombre AS grupo,
                       COALESCE(c.nombre, 'Otros') AS categoria,
                       g.descripcion, u.name AS pagador, g.monto
                  FROM gastos g
                  JOIN grupos gr ON gr.id = g.grupo_id
                  JOIN users u ON u.id = g.pagador_id
                  JOIN grupo_miembros gm ON gm.grupo_id = g.grupo_id AND gm.user_id = ?
                  LEFT JOIN categorias c ON c.id = g.categoria_id
                 WHERE {$where['sql']}
                 ORDER BY g.fecha DESC, g.created_at DESC";

        return $db->query($sql, $where['binds'])->getResultArray();
    }

    /**
     * Metodo puro para calcular el resumen mostrado en reportes.
     */
    public static function calcularResumen(
        float $totalGastado,
        float $totalPagado,
        float $totalRecibido,
        float $totalConsumido,
        float $totalPagosEnviados = 0.0
    ): array
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

    /**
     * Filtra filas de gastos en memoria. Replica las condiciones usadas por las queries.
     */
    public static function aplicarFiltros(array $rows, array $filters = []): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($filters): bool {
            if (!empty($filters['grupo_id']) && (int) ($row['grupo_id'] ?? 0) !== (int) $filters['grupo_id']) {
                return false;
            }

            if (!empty($filters['categoria_id']) && (int) ($row['categoria_id'] ?? 0) !== (int) $filters['categoria_id']) {
                return false;
            }

            $fecha = (string) ($row['fecha'] ?? '');
            if (!empty($filters['fecha_desde']) && $fecha < (string) $filters['fecha_desde']) {
                return false;
            }

            if (!empty($filters['fecha_hasta']) && $fecha > (string) $filters['fecha_hasta']) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Agrupa gastos por categoria usando filas en memoria.
     */
    public static function agruparGastosPorCategoria(array $rows): array
    {
        $agrupado = [];
        foreach ($rows as $row) {
            $categoria = (string) ($row['categoria'] ?? $row['categoria_nombre'] ?? 'Otros');
            if ($categoria === '') {
                $categoria = 'Otros';
            }

            if (!isset($agrupado[$categoria])) {
                $agrupado[$categoria] = ['categoria' => $categoria, 'cantidad' => 0, 'total' => 0.0];
            }

            $agrupado[$categoria]['cantidad']++;
            $agrupado[$categoria]['total'] += (float) ($row['monto'] ?? 0);
        }

        return self::ordenarAgrupadoPorTotal($agrupado);
    }

    /**
     * Agrupa gastos por grupo usando filas en memoria.
     */
    public static function agruparGastosPorGrupo(array $rows): array
    {
        $agrupado = [];
        foreach ($rows as $row) {
            $grupo = (string) ($row['grupo'] ?? $row['grupo_nombre'] ?? 'Sin grupo');
            if ($grupo === '') {
                $grupo = 'Sin grupo';
            }

            if (!isset($agrupado[$grupo])) {
                $agrupado[$grupo] = ['grupo' => $grupo, 'cantidad' => 0, 'total' => 0.0];
            }

            $agrupado[$grupo]['cantidad']++;
            $agrupado[$grupo]['total'] += (float) ($row['monto'] ?? 0);
        }

        return self::ordenarAgrupadoPorTotal($agrupado);
    }

    /**
     * Toma un arreglo de gastos y devuelve lineas CSV listas para fputcsv.
     */
    public static function formatearFilasCsv(array $rows): array
    {
        $lineas = [];
        foreach ($rows as $r) {
            $lineas[] = [
                $r['fecha'] ?? '',
                $r['grupo'] ?? '',
                $r['categoria'] ?? '',
                $r['descripcion'] ?? '',
                $r['pagador'] ?? '',
                number_format((float) ($r['monto'] ?? 0), 2, '.', ''),
            ];
        }

        return $lineas;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private static function gruposIds(int $userId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->query(
            'SELECT grupo_id FROM grupo_miembros WHERE user_id = ?',
            [$userId]
        )->getResultArray();

        return array_map('intval', array_column($rows, 'grupo_id'));
    }

    /**
     * Construye clausula WHERE y binds para filtros reutilizables.
     * El primer bind, userId, corresponde al JOIN grupo_miembros.
     */
    private static function buildWhere(int $userId, array $filters, string $alias): array
    {
        $binds = [$userId];
        $clauses = [];

        if (!empty($filters['grupo_id'])) {
            $clauses[] = "{$alias}.grupo_id = ?";
            $binds[] = (int) $filters['grupo_id'];
        }

        if (!empty($filters['categoria_id'])) {
            $clauses[] = "{$alias}.categoria_id = ?";
            $binds[] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['fecha_desde'])) {
            $clauses[] = "{$alias}.fecha >= ?";
            $binds[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $clauses[] = "{$alias}.fecha <= ?";
            $binds[] = $filters['fecha_hasta'];
        }

        return ['sql' => $clauses ? implode(' AND ', $clauses) : '1=1', 'binds' => $binds];
    }

    private static function scalarTotal(?object $row): float
    {
        return round((float) ($row->total ?? 0), 2);
    }

    private static function ordenarAgrupadoPorTotal(array $agrupado): array
    {
        $rows = array_values(array_map(static function (array $row): array {
            $row['total'] = round((float) $row['total'], 2);
            return $row;
        }, $agrupado));

        usort($rows, static function (array $a, array $b): int {
            $byTotal = $b['total'] <=> $a['total'];
            if ($byTotal !== 0) {
                return $byTotal;
            }

            $aName = (string) ($a['categoria'] ?? $a['grupo'] ?? '');
            $bName = (string) ($b['categoria'] ?? $b['grupo'] ?? '');
            return strcmp($aName, $bName);
        });

        return $rows;
    }
}
