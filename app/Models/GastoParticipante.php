<?php

namespace App\Models;

use CodeIgniter\Model;

class GastoParticipante extends Model
{
    protected $table = 'gasto_participantes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['gasto_id', 'user_id', 'monto_asignado'];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = '';
}
