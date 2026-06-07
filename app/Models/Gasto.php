<?php

namespace App\Models;

use CodeIgniter\Model;

use App\Models\GrupoMiembro;

class Gasto extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['grupo_id', 'pagador_id', 'descripcion', 'monto', 'fecha'];
    protected $useTimestamps = true;

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

    public function getDeudasByGrupo(int $grupoId): array
    {
        $saldos = $this->getSaldosByGrupo($grupoId);

        $acreedores = array_filter($saldos, fn($s) => $s['saldo'] > 0);
        $deudores = array_filter($saldos, fn($s) => $s['saldo'] < 0);

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
}
