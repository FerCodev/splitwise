<?php

namespace App\Models;

use CodeIgniter\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'created_by'];
    protected $useTimestamps = true;

    public function getGruposByUser(int $userId): array
    {
        return $this->select('grupos.*')
            ->join('grupo_miembros', 'grupo_miembros.grupo_id = grupos.id')
            ->where('grupo_miembros.user_id', $userId)
            ->orderBy('grupos.created_at', 'DESC')
            ->findAll();
    }

    public function isMiembro(int $grupoId, int $userId): bool
    {
        return $this->db->table('grupo_miembros')
            ->where('grupo_id', $grupoId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }

    public function getUserRol(int $grupoId, int $userId): ?string
    {
        $row = $this->db->table('grupo_miembros')
            ->select('rol')
            ->where('grupo_id', $grupoId)
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return $row ? $row->rol : null;
    }

    public function getMiembros(int $grupoId): array
    {
        return $this->db->table('grupo_miembros')
            ->select('grupo_miembros.*, users.name, users.email')
            ->join('users', 'users.id = grupo_miembros.user_id')
            ->where('grupo_miembros.grupo_id', $grupoId)
            ->orderBy('grupo_miembros.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function existsByNameForUser(string $nombre, int $userId, ?int $excludeId = null): bool
    {
        $this->where('nombre', $nombre);
        $this->where('created_by', $userId);

        if ($excludeId !== null) {
            $this->where('id !=', $excludeId);
        }

        return $this->countAllResults() > 0;
    }

    public function getTotalGastado(int $grupoId): float
    {
        $result = $this->db->table('gastos')
            ->selectSum('monto')
            ->where('grupo_id', $grupoId)
            ->get()
            ->getRow();

        return (float) ($result->monto ?? 0);
    }
}
