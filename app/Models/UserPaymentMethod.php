<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPaymentMethod extends Model
{
    protected $table = 'user_payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'tipo', 'nombre', 'alias', 'cbu_cvu', 'banco',
        'titular', 'payment_link', 'activo', 'favorito',
    ];
    protected $useTimestamps = true;

    public function getActivosByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('activo', 1)
            ->orderBy('favorito', 'DESC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function marcarFavorito(int $id, int $userId): void
    {
        $this->where('user_id', $userId)
            ->where('favorito', 1)
            ->set(['favorito' => 0])
            ->update();

        $this->update($id, ['favorito' => 1]);
    }

    public function quitarFavorito(int $id, int $userId): void
    {
        $this->where('id', $id)
            ->where('user_id', $userId)
            ->set(['favorito' => 0])
            ->update();
    }

}
