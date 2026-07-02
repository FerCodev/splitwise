<?php

namespace App\Models;

use CodeIgniter\Model;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['grupo_id', 'pagador_id', 'descripcion', 'monto', 'fecha', 'categoria_id', 'division_tipo', 'nota', 'recibo_path', 'recibo_nombre', 'recibo_mime', 'recibo_size'];
    protected $useTimestamps = true;

    public function getGastosByGrupo(int $grupoId): array
    {
        return $this->select('gastos.*, categorias.nombre as categoria_nombre, users.name as pagador_nombre,
                users.avatar_filename as pagador_avatar_filename, users.avatar_updated_at as pagador_avatar_updated_at,
                (SELECT COUNT(*) FROM gasto_participantes WHERE gasto_id = gastos.id) as total_participantes')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('categorias', 'categorias.id = gastos.categoria_id', 'left')
            ->where('gastos.grupo_id', $grupoId)
            ->orderBy('gastos.fecha', 'DESC')
            ->orderBy('gastos.created_at', 'DESC')
            ->findAll();
    }

    public function getGastosWithFilters(array $filters, int $perPage = 10): array
    {
        $userId = session()->get('userId');

        $this->select('gastos.*, categorias.nombre as categoria_nombre, users.name as pagador_nombre, grupos.nombre as grupo_nombre,
                (SELECT COUNT(*) FROM gasto_participantes WHERE gasto_id = gastos.id) as total_participantes')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->join('categorias', 'categorias.id = gastos.categoria_id', 'left')
            ->join('grupo_miembros', 'grupo_miembros.grupo_id = gastos.grupo_id AND grupo_miembros.user_id = ' . $userId)
            ->where('gastos.pagador_id', $userId)
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

        if (!empty($filters['categoria_id'])) {
            $this->where('gastos.categoria_id', $filters['categoria_id']);
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

        return $this->paginate($perPage);
    }

    public function getMontosPorCategoria(int $grupoId): array
    {
        return $this->select('categorias.nombre as categoria_nombre, SUM(gastos.monto) as total, COUNT(*) as cantidad')
            ->join('categorias', 'categorias.id = gastos.categoria_id', 'left')
            ->where('gastos.grupo_id', $grupoId)
            ->groupBy('gastos.categoria_id')
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

    /**
     * Calcula el balance completo por usuario incluyendo pagos.
     *
     * saldo = total_pagado_gastos - total_consumido - pagos_recibidos + pagos_enviados
     *
     * Los pagos recibidos REDUCEN el saldo (la deuda del otro se extingue).
     * Los pagos enviados AUMENTAN el saldo (la deuda propia se extingue).
     *
     * Fuente primaria del balance: gasto_divisiones.monto_calculado
     * (gastos con divisiones explicitas).
     * Fallback legacy: gasto_participantes.monto_asignado
     * (gastos sin divisiones, previos a UX4).
     * Al crear/editar un gasto, ambas tablas deben escribirse desde el
     * mismo calculo para mantener sincronia.
     */
    public function getBalanceByGrupo(int $grupoId): array
    {
        $db = $this->db;
        $miembros = model(GrupoMiembro::class)
            ->select('grupo_miembros.*, users.name, users.email, users.avatar_filename, users.avatar_updated_at')
            ->join('users', 'users.id = grupo_miembros.user_id')
            ->where('grupo_miembros.grupo_id', $grupoId)
            ->findAll();

        $pagadoGastos = $this->sumByUser('gastos', 'pagador_id', $grupoId);

        $consumido = [];
        $db = $this->db;
        $gastosConDivision = $db->table('gasto_divisiones')
            ->select('gasto_divisiones.user_id, SUM(gasto_divisiones.monto_calculado) as total')
            ->join('gastos', 'gastos.id = gasto_divisiones.gasto_id')
            ->where('gastos.grupo_id', $grupoId)
            ->groupBy('gasto_divisiones.user_id')
            ->get()
            ->getResultArray();
        foreach ($gastosConDivision as $r) {
            $consumido[$r['user_id']] = (float) $r['total'];
        }

        $gastosSinDivision = $db->table('gastos')
            ->select('gastos.id')
            ->where('gastos.grupo_id', $grupoId)
            ->whereNotIn('gastos.id', function ($qb) {
                $qb->select('gasto_id')->from('gasto_divisiones');
            })
            ->get()
            ->getResultArray();

        if (!empty($gastosSinDivision)) {
            $ids = array_column($gastosSinDivision, 'id');
            $rows = $db->table('gasto_participantes')
                ->select('gasto_participantes.user_id, SUM(gasto_participantes.monto_asignado) as total')
                ->whereIn('gasto_participantes.gasto_id', $ids)
                ->groupBy('gasto_participantes.user_id')
                ->get()
                ->getResultArray();
            foreach ($rows as $r) {
                $consumido[$r['user_id']] = ($consumido[$r['user_id']] ?? 0) + (float) $r['total'];
            }
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
     * Calcula el monto asignado a cada participante segun el modo de division.
     * Metodo puro, sin side effects, testeable sin base de datos.
     *
     * @param string $divisionTipo igualitario|monto_fijo|porcentaje
     * @param float  $monto        total del gasto
     * @param int[]  $participantesIds  ids de participantes
     * @param array  $divisionValores  raw del form: [['user_id'=>int,'valor'=>string],...]
     * @return float[]  [user_id => monto_calculado]
     */
    public static function calcularMontosDivision(
        string $divisionTipo,
        float $monto,
        array $participantesIds,
        array $divisionValores = []
    ): array {
        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $participantesMonto = [];

        if ($divisionTipo === 'monto_fijo' && !empty($divisionValores)) {
            $valoresMap = [];
            foreach ($divisionValores as $dv) {
                $valoresMap[(int) $dv['user_id']] = (float) $dv['valor'];
            }
            foreach ($participantesIds as $uid) {
                $participantesMonto[$uid] = round($valoresMap[$uid] ?? 0, 2);
            }
        } elseif ($divisionTipo === 'porcentaje' && !empty($divisionValores)) {
            $valoresMap = [];
            foreach ($divisionValores as $dv) {
                $valoresMap[(int) $dv['user_id']] = (float) $dv['valor'];
            }
            $totalCalc = 0;
            foreach ($participantesIds as $i => $uid) {
                $calc = round($monto * ($valoresMap[$uid] ?? 0) / 100, 2);
                $participantesMonto[$uid] = $calc;
                $totalCalc += $calc;
            }
            $diff = round($monto - $totalCalc, 2);
            if (abs($diff) > 0.001 && !empty($participantesIds)) {
                $lastUid = end($participantesIds);
                $participantesMonto[$lastUid] = round($participantesMonto[$lastUid] + $diff, 2);
            }
        } else {
            $porcion = round($monto / count($participantesIds), 2);
            $diferencias = round($monto - ($porcion * count($participantesIds)), 2);
            foreach ($participantesIds as $i => $uid) {
                $asignado = $porcion;
                if ($i === array_key_last($participantesIds)) {
                    $asignado += $diferencias;
                }
                $participantesMonto[$uid] = round($asignado, 2);
            }
        }

        return $participantesMonto;
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
                    'deudor_avatar_filename' => $d['avatar_filename'] ?? null,
                    'deudor_avatar_updated_at' => $d['avatar_updated_at'] ?? null,
                    'acreedor' => $a['name'],
                    'acreedor_id' => $a['user_id'],
                    'acreedor_avatar_filename' => $a['avatar_filename'] ?? null,
                    'acreedor_avatar_updated_at' => $a['avatar_updated_at'] ?? null,
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

    public function getDeudaVigente(int $grupoId, int $deudorId, int $acreedorId): ?array
    {
        $deudas = $this->getDeudasByGrupo($grupoId);

        foreach ($deudas as $d) {
            if ((int) $d['deudor_id'] === $deudorId && (int) $d['acreedor_id'] === $acreedorId) {
                return $d;
            }
        }

        return null;
    }
}
