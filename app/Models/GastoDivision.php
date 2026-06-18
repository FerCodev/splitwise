<?php

namespace App\Models;

use CodeIgniter\Model;

class GastoDivision extends Model
{
    protected $table = 'gasto_divisiones';
    protected $primaryKey = 'id';
    protected $allowedFields = ['gasto_id', 'user_id', 'tipo', 'valor', 'monto_calculado'];
    protected $useTimestamps = true;

}
