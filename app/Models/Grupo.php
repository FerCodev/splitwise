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
}
