<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoMiembro extends Model
{
    protected $table = 'grupo_miembros';
    protected $primaryKey = 'id';
    protected $allowedFields = ['grupo_id', 'user_id', 'rol'];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = '';
}
