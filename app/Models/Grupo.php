<?php

namespace App\Models;

use CodeIgniter\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'created_by', 'estado'];
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

    // ---------------------------------------------------------------
    // Estado del grupo
    // ---------------------------------------------------------------

    /**
     * Computa el resumen financiero del dashboard a partir de grupos
     * enriquecidos con mi_saldo. Metodo puro, testeable sin DB.
     *
     * @param array $grupos Lista de grupos con clave 'estado' y 'mi_saldo'
     * @return array [cantidadActivos, cantidadCerrados, cantidadLiquidados, globalSaldo, gruposAFavor, gruposDebe]
     */
    public static function computeDashboardResumen(array $grupos): array
    {
        $cantidadActivos = 0;
        $cantidadCerrados = 0;
        $cantidadLiquidados = 0;
        $globalSaldo = 0.0;
        $gruposAFavor = 0;
        $gruposDebe = 0;

        foreach ($grupos as $g) {
            $estado = $g['estado'] ?? 'activo';
            if ($estado === 'activo') {
                $cantidadActivos++;
            } elseif ($estado === 'cerrado') {
                $cantidadCerrados++;
            } elseif ($estado === 'liquidado') {
                $cantidadLiquidados++;
            }

            $saldo = (float) ($g['mi_saldo'] ?? 0);
            $globalSaldo += $saldo;
            if ($saldo > 0) {
                $gruposAFavor++;
            } elseif ($saldo < 0) {
                $gruposDebe++;
            }
        }

        return [
            'cantidadActivos' => $cantidadActivos,
            'cantidadCerrados' => $cantidadCerrados,
            'cantidadLiquidados' => $cantidadLiquidados,
            'globalSaldo' => round($globalSaldo, 2),
            'gruposAFavor' => $gruposAFavor,
            'gruposDebe' => $gruposDebe,
        ];
    }

    /**
     * Estados posibles y sus transiciones permitidas.
     * clave => lista de estados a los que se puede transicionar.
     */
    public static function transiciones(): array
    {
        return [
            'activo'     => ['cerrado'],
            'cerrado'    => ['activo', 'liquidado'],
            'liquidado'  => [],
        ];
    }

    /**
     * Indica si la transicion de $desde a $hasta es valida.
     */
    public static function transicionValida(string $desde, string $hasta): bool
    {
        $siguientes = self::transiciones()[$desde] ?? [];
        return in_array($hasta, $siguientes, true);
    }

    /**
     * Retorna mensaje de error si la accion no esta permitida para el estado,
     * o null si esta permitida.
     *
     * Acciones: gasto_create, gasto_edit, gasto_delete,
     *           pago_create, pago_edit, pago_delete,
     *           grupo_edit, grupo_delete
     */
    public static function restriccionEstado(string $estado, string $accion): ?string
    {
        $bloqueos = [
            'gasto_create' => ['cerrado' => 'No se pueden crear gastos en un grupo cerrado.', 'liquidado' => 'No se pueden crear gastos en un grupo liquidado.'],
            'gasto_edit'   => ['cerrado' => 'No se pueden editar gastos en un grupo cerrado.', 'liquidado' => 'No se pueden editar gastos en un grupo liquidado.'],
            'gasto_delete' => ['cerrado' => 'No se pueden eliminar gastos en un grupo cerrado.', 'liquidado' => 'No se pueden eliminar gastos en un grupo liquidado.'],
            'pago_create'  => ['liquidado' => 'No se pueden registrar pagos en un grupo liquidado.'],
            'pago_edit'    => ['liquidado' => 'No se pueden editar pagos en un grupo liquidado.'],
            'pago_delete'  => ['liquidado' => 'No se pueden eliminar pagos en un grupo liquidado.'],
            'grupo_edit'   => ['liquidado' => 'No se puede editar un grupo liquidado.'],
            'grupo_delete' => ['cerrado' => 'No se puede eliminar un grupo cerrado.', 'liquidado' => 'No se puede eliminar un grupo liquidado.'],
        ];

        $porEstado = $bloqueos[$accion] ?? [];
        return $porEstado[$estado] ?? null;
    }
}
