<?php

namespace App\Models;

use CodeIgniter\Model;

use App\Models\GrupoMiembro;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['grupo_id', 'pagador_id', 'descripcion', 'monto', 'fecha', 'categoria'];
    protected $useTimestamps = true;

    public static function categoriasPermitidas(): array
    {
        return [
            'Supermercado',
            'Servicios',
            'Combustible',
            'Farmacia',
            'Mascotas',
            'Transporte',
            'Comida',
            'Viajes',
            'Otros',
        ];
    }

    public function getGastosByGrupo(int $grupoId): array
    {
        return $this->select('gastos.*, users.name as pagador_nombre, 
                (SELECT COUNT(*) FROM gasto_participantes WHERE gasto_id = gastos.id) as total_participantes')
            ->join('users', 'users.id = gastos.pagador_id')
            ->where('gastos.grupo_id', $grupoId)
            ->orderBy('gastos.fecha', 'DESC')
            ->orderBy('gastos.created_at', 'DESC')
            ->findAll();
    }

    public function getGastosWithFilters(array $filters): array
    {
        $userId = session()->get('userId');

        $this->select('gastos.*, users.name as pagador_nombre, grupos.nombre as grupo_nombre,
                (SELECT COUNT(*) FROM gasto_participantes WHERE gasto_id = gastos.id) as total_participantes')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->join('grupo_miembros', 'grupo_miembros.grupo_id = gastos.grupo_id AND grupo_miembros.user_id = ' . $userId)
            ->groupBy('gastos.id');

        if (!empty($filters['grupo_id'])) {
            $this->where('gastos.grupo_id', $filters['grupo_id']);
        }

        if (!empty($filters['pagador_id'])) {
            $this->where('gastos.pagador_id', $filters['pagador_id']);
        }

        if (!empty($filters['fecha_desde'])) {
            $this->where('gastos.fecha >=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $this->where('gastos.fecha <=', $filters['fecha_hasta']);
        }

        if (!empty($filters['descripcion'])) {
            $this->like('gastos.descripcion', $filters['descripcion']);
        }

        if (!empty($filters['categoria'])) {
            $this->where('gastos.categoria', $filters['categoria']);
        }

        $sort = $filters['sort'] ?? 'fecha';
        $order = $filters['order'] ?? 'DESC';

        $allowedSorts = ['fecha', 'monto', 'grupo_nombre'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'fecha';
        }

        if ($sort === 'grupo_nombre') {
            $this->orderBy('grupos.nombre', $order);
        } else {
            $this->orderBy('gastos.' . $sort, $order);
        }

        return $this->findAll();
    }

    public function getMontosPorCategoria(int $grupoId): array
    {
        return $this->select('categoria, SUM(monto) as total, COUNT(*) as cantidad')
            ->where('grupo_id', $grupoId)
            ->groupBy('categoria')
            ->orderBy('total', 'DESC')
            ->findAll();
    }

    public function getParticipantes(int $gastoId): array
    {
        return $this->db->table('gasto_participantes')
            ->select('gasto_participantes.*, users.name, users.email')
            ->join('users', 'users.id = gasto_participantes.user_id')
            ->where('gasto_participantes.gasto_id', $gastoId)
            ->get()
            ->getResultArray();
    }

    public function getSaldosByGrupo(int $grupoId): array
    {
        $gastos = $this->where('grupo_id', $grupoId)->findAll();
        $miembros = model(GrupoMiembro::class)
            ->select('grupo_miembros.*, users.name, users.email')
            ->join('users', 'users.id = grupo_miembros.user_id')
            ->where('grupo_miembros.grupo_id', $grupoId)
            ->findAll();

        $saldos = [];
        foreach ($miembros as $m) {
            $saldos[$m['user_id']] = [
                'user_id' => $m['user_id'],
                'name' => $m['name'],
                'pago' => 0,
                'debe' => 0,
                'saldo' => 0,
            ];
        }

        foreach ($gastos as $gasto) {
            $participantes = $this->getParticipantes($gasto['id']);
            $pagadorId = $gasto['pagador_id'];

            foreach ($participantes as $p) {
                if ($p['user_id'] == $pagadorId) {
                    continue;
                }
                if (isset($saldos[$pagadorId])) {
                    $saldos[$pagadorId]['pago'] += $p['monto_asignado'];
                }
                if (isset($saldos[$p['user_id']])) {
                    $saldos[$p['user_id']]['debe'] += $p['monto_asignado'];
                }
            }
        }

        foreach ($saldos as &$s) {
            $s['saldo'] = $s['pago'] - $s['debe'];
        }

        usort($saldos, function ($a, $b) {
            return $b['saldo'] - $a['saldo'];
        });

        return $saldos;
    }

    /**
     * Calcula el balance completo por usuario incluyendo pagos.
     *
     * saldo = total_pagado_gastos - total_consumido - pagos_recibidos + pagos_enviados
     *
     * Los pagos recibidos REDUCEN el saldo (la deuda del otro se extingue).
     * Los pagos enviados AUMENTAN el saldo (la deuda propia se extingue).
     */
    public function getBalanceByGrupo(int $grupoId): array
    {
        $db = $this->db;
        $miembros = model(GrupoMiembro::class)
            ->select('grupo_miembros.*, users.name, users.email')
            ->join('users', 'users.id = grupo_miembros.user_id')
            ->where('grupo_miembros.grupo_id', $grupoId)
            ->findAll();

        $pagadoGastos = $this->sumByUser('gastos', 'pagador_id', $grupoId);
        $consumido = [];
        $rows = $db->table('gasto_participantes')
            ->select('gasto_participantes.user_id, SUM(gasto_participantes.monto_asignado) as total')
            ->join('gastos', 'gastos.id = gasto_participantes.gasto_id')
            ->where('gastos.grupo_id', $grupoId)
            ->groupBy('gasto_participantes.user_id')
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $consumido[$r['user_id']] = (float) $r['total'];
        }

        $pagosEnviados = $this->sumByUser('pagos', 'pagador_id', $grupoId);
        $pagosRecibidos = $this->sumByUser('pagos', 'receptor_id', $grupoId);

        return self::computeBalance($miembros, $pagadoGastos, $consumido, $pagosEnviados, $pagosRecibidos);
    }

    private function sumByUser(string $table, string $userIdColumn, int $grupoId): array
    {
        $result = [];
        $rows = $this->db->table($table)
            ->select("$userIdColumn as user_id, SUM(monto) as total")
            ->where('grupo_id', $grupoId)
            ->groupBy($userIdColumn)
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $result[$r['user_id']] = (float) $r['total'];
        }
        return $result;
    }

    /**
     * Computa el balance a partir de datos pre-cargados.
     * Separado para poder testear sin base de datos.
     */
    public static function computeBalance(
        array $miembros,
        array $pagadoGastos,
        array $consumido,
        array $pagosEnviados,
        array $pagosRecibidos
    ): array {
        $balance = [];
        foreach ($miembros as $m) {
            $uid = $m['user_id'];
            $tp = (float) ($pagadoGastos[$uid] ?? 0);
            $tc = (float) ($consumido[$uid] ?? 0);
            $pe = (float) ($pagosEnviados[$uid] ?? 0);
            $pr = (float) ($pagosRecibidos[$uid] ?? 0);
            $balance[] = [
                'user_id' => $uid,
                'name' => $m['name'],
                'total_pagado_gastos' => $tp,
                'total_consumido' => $tc,
                'pagos_enviados' => $pe,
                'pagos_recibidos' => $pr,
                'saldo' => round($tp - $tc - $pr + $pe, 2),
            ];
        }

        usort($balance, fn($a, $b) => $b['saldo'] - $a['saldo']);

        return $balance;
    }

    /**
     * Calcula las transferencias sugeridas a partir de un array de balance.
     * Separado como metodo puro para poder testear sin base de datos.
     *
     * @param array $balance resultado de computeBalance() o getBalanceByGrupo()
     * @return array listado de deudas con deudor, acreedor, monto
     */
    public static function computeDeudasFromBalance(array $balance): array
    {
        $acreedores = array_filter($balance, fn($s) => $s['saldo'] > 0);
        $deudores = array_filter($balance, fn($s) => $s['saldo'] < 0);

        $deudas = [];
        foreach ($deudores as $d) {
            $restante = abs($d['saldo']);
            foreach ($acreedores as &$a) {
                if ($restante <= 0) break;
                if ($a['saldo'] <= 0) continue;

                $monto = min($restante, $a['saldo']);
                $deudas[] = [
                    'deudor' => $d['name'],
                    'deudor_id' => $d['user_id'],
                    'acreedor' => $a['name'],
                    'acreedor_id' => $a['user_id'],
                    'monto' => round($monto, 2),
                ];
                $restante -= $monto;
                $a['saldo'] -= $monto;
            }
        }

        return $deudas;
    }

    public function getDeudasByGrupo(int $grupoId): array
    {
        return self::computeDeudasFromBalance($this->getBalanceByGrupo($grupoId));
    }

    /**
     * Retorna los ultimos gastos visibles para el usuario en todos sus grupos.
     */
    public function getUltimosGastosByUser(int $userId, int $limit = 5): array
    {
        return $this->select('gastos.*, users.name as pagador_nombre, grupos.nombre as grupo_nombre')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->join('grupo_miembros', 'grupo_miembros.grupo_id = gastos.grupo_id AND grupo_miembros.user_id = ' . $userId)
            ->groupBy('gastos.id')
            ->orderBy('gastos.fecha', 'DESC')
            ->orderBy('gastos.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
