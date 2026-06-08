<?php

namespace App\Models;

use CodeIgniter\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'activa'];
    protected $useTimestamps = true;

    public const PROTEGIDA = 'Otros';

    public static function isProtegida(string $nombre): bool
    {
        return $nombre === self::PROTEGIDA;
    }

    public function getActivas(): array
    {
        return $this->where('activa', 1)->orderBy('id', 'ASC')->findAll();
    }

    public function getOtrosId(): int
    {
        $row = $this->select('id')->where('nombre', self::PROTEGIDA)->get()->getRow();
        return $row ? (int) $row->id : 0;
    }

    public function esUsadaPorGastos(int $id): bool
    {
        return $this->db->table('gastos')->where('categoria_id', $id)->countAllResults() > 0;
    }
}
