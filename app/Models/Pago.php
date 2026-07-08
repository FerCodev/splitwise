<?php

namespace App\Models;

use CodeIgniter\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['grupo_id', 'pagador_id', 'receptor_id', 'monto', 'fecha', 'descripcion'];
    protected $useTimestamps = true;

    public function getPagosByGrupo(int $grupoId): array
    {
        return $this->select('pagos.*, pagador.name as pagador_nombre, receptor.name as receptor_nombre, pagador.avatar_filename as pagador_avatar_filename, pagador.avatar_updated_at as pagador_avatar_updated_at, receptor.avatar_filename as receptor_avatar_filename, receptor.avatar_updated_at as receptor_avatar_updated_at')
            ->join('users as pagador', 'pagador.id = pagos.pagador_id')
            ->join('users as receptor', 'receptor.id = pagos.receptor_id')
            ->where('pagos.grupo_id', $grupoId)
            ->orderBy('pagos.fecha', 'DESC')
            ->orderBy('pagos.created_at', 'DESC')
            ->findAll();
    }

    public function getPagosWithFilters(array $filters, int $perPage = 10): array
    {
        $this->applyFilteredListQuery($filters);
        return $this->paginate($perPage);
    }

    public function getPagosForExport(array $filters): array
    {
        $this->applyFilteredListQuery($filters);
        return $this->findAll();
    }

    private function applyFilteredListQuery(array $filters): void
    {
        $userId = (int) session()->get('userId');

        $this->select('pagos.*, pagador.name as pagador_nombre, receptor.name as receptor_nombre, pagador.avatar_filename as pagador_avatar_filename, pagador.avatar_updated_at as pagador_avatar_updated_at, receptor.avatar_filename as receptor_avatar_filename, receptor.avatar_updated_at as receptor_avatar_updated_at, grupos.nombre as grupo_nombre')
            ->join('users as pagador', 'pagador.id = pagos.pagador_id')
            ->join('users as receptor', 'receptor.id = pagos.receptor_id')
            ->join('grupos', 'grupos.id = pagos.grupo_id')
            ->join('grupo_miembros', 'grupo_miembros.grupo_id = pagos.grupo_id AND grupo_miembros.user_id = ' . $userId)
            ->groupStart()
                ->where('pagos.pagador_id', $userId)
                ->orWhere('pagos.receptor_id', $userId)
            ->groupEnd()
            ->groupBy('pagos.id');

        if (!empty($filters['grupo_id'])) {
            $this->where('pagos.grupo_id', $filters['grupo_id']);
        }

        if (!empty($filters['pagador_id'])) {
            $this->where('pagos.pagador_id', $filters['pagador_id']);
        }

        if (!empty($filters['receptor_id'])) {
            $this->where('pagos.receptor_id', $filters['receptor_id']);
        }

        if (!empty($filters['fecha_desde'])) {
            $this->where('pagos.fecha >=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $this->where('pagos.fecha <=', $filters['fecha_hasta']);
        }

        if (!empty($filters['descripcion'])) {
            $this->like('pagos.descripcion', $filters['descripcion']);
        }

        $sort = $filters['sort'] ?? 'fecha';
        $order = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $allowedSorts = ['fecha', 'monto', 'grupo_nombre'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'fecha';
        }

        if ($sort === 'grupo_nombre') {
            $this->orderBy('grupos.nombre', $order);
        } else {
            $this->orderBy('pagos.' . $sort, $order);
        }
    }

    public function getTotalPagadoByGrupo(int $grupoId): float
    {
        $result = $this->selectSum('monto')
            ->where('grupo_id', $grupoId)
            ->get()
            ->getRow();

        return (float) ($result->monto ?? 0);
    }
}
