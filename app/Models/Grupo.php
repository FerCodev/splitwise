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
            'grupo_edit'     => ['liquidado' => 'No se puede editar un grupo liquidado.'],
            'grupo_delete'   => ['cerrado' => 'No se puede eliminar un grupo cerrado.', 'liquidado' => 'No se puede eliminar un grupo liquidado.'],
            'miembro_create' => ['cerrado' => 'No se pueden agregar miembros a un grupo cerrado.', 'liquidado' => 'No se pueden agregar miembros a un grupo liquidado.'],
            'miembro_role'   => ['cerrado' => 'No se puede cambiar el rol en un grupo cerrado.', 'liquidado' => 'No se puede cambiar el rol en un grupo liquidado.'],
            'miembro_delete' => ['cerrado' => 'No se puede quitar miembros de un grupo cerrado.', 'liquidado' => 'No se puede quitar miembros de un grupo liquidado.'],
        ];

        $porEstado = $bloqueos[$accion] ?? [];
        return $porEstado[$estado] ?? null;
    }

    // ---------------------------------------------------------------
    // Gestion de miembros
    // ---------------------------------------------------------------

    public function countAdmins(int $grupoId): int
    {
        return $this->db->table('grupo_miembros')
            ->where('grupo_id', $grupoId)
            ->where('rol', 'admin')
            ->countAllResults();
    }

    public function miembroTieneMovimientos(int $grupoId, int $userId): bool
    {
        $db = $this->db;
        $gastos = $db->table('gastos')
            ->where('grupo_id', $grupoId)
            ->where('pagador_id', $userId)
            ->countAllResults();

        if ($gastos > 0) {
            return true;
        }

        $participante = $db->table('gasto_participantes')
            ->join('gastos', 'gastos.id = gasto_participantes.gasto_id')
            ->where('gastos.grupo_id', $grupoId)
            ->where('gasto_participantes.user_id', $userId)
            ->countAllResults();

        if ($participante > 0) {
            return true;
        }

        $pagosPagador = $db->table('pagos')
            ->where('grupo_id', $grupoId)
            ->where('pagador_id', $userId)
            ->countAllResults();

        if ($pagosPagador > 0) {
            return true;
        }

        $pagosReceptor = $db->table('pagos')
            ->where('grupo_id', $grupoId)
            ->where('receptor_id', $userId)
            ->countAllResults();

        return $pagosReceptor > 0;
    }

    /**
     * Retorna un array asociativo [grupo_id => ultima_actividad] para todos
     * los grupos del usuario. La actividad se calcula como la fecha mas
     * reciente entre: ultimo gasto, ultimo pago, updated_at, created_at.
     */
    public function getUltimaActividadByUser(int $userId): array
    {
        $sql = "SELECT g.id as grupo_id,
                       GREATEST(
                           COALESCE(MAX(gast.fecha), '1970-01-01'),
                           COALESCE(MAX(pag.fecha), '1970-01-01'),
                           g.updated_at,
                           g.created_at
                       ) as ultima_actividad
                  FROM grupos g
                  JOIN grupo_miembros gm ON gm.grupo_id = g.id AND gm.user_id = ?
                  LEFT JOIN gastos gast ON gast.grupo_id = g.id
                  LEFT JOIN pagos pag ON pag.grupo_id = g.id
                 GROUP BY g.id";

        $rows = $this->db->query($sql, [$userId])->getResultArray();
        $result = [];
        foreach ($rows as $r) {
            $result[(int) $r['grupo_id']] = $r['ultima_actividad'];
        }
        return $result;
    }

    public function getUsuariosDisponibles(int $grupoId): array
    {
        $sql = 'SELECT id, name, email FROM users WHERE id NOT IN (
                    SELECT user_id FROM grupo_miembros WHERE grupo_id = ?
                ) ORDER BY name ASC';

        return $this->db->query($sql, [$grupoId])->getResultArray();
    }

    // ---------------------------------------------------------------
    // Reglas de negocio para miembros (puras, testeables sin DB)
    // ---------------------------------------------------------------

    /**
     * Indica si se puede quitar un miembro del grupo segun las reglas.
     *
     * @param int $totalAdmins cantidad de admins en el grupo
     * @param string $rol del miembro a quitar
     * @param bool $tieneMovimientos si el miembro tiene gastos/pagos
     * @return string|null mensaje de error o null si permitido
     */
    public static function puedeQuitarMiembro(int $totalAdmins, string $rol, bool $tieneMovimientos): ?string
    {
        if ($rol === 'admin' && $totalAdmins <= 1) {
            return 'No se puede quitar el último administrador del grupo.';
        }

        if ($tieneMovimientos) {
            return 'No se puede quitar este miembro porque tiene movimientos en el grupo.';
        }

        return null;
    }

    /**
     * Indica si se puede cambiar el rol de un miembro.
     *
     * @param int $totalAdmins cantidad de admins en el grupo
     * @param string $rolActual del miembro
     * @param string $nuevoRol deseado
     * @return string|null mensaje de error o null si permitido
     */
    public static function puedeCambiarRol(int $totalAdmins, string $rolActual, string $nuevoRol): ?string
    {
        if ($rolActual === 'admin' && $nuevoRol === 'member' && $totalAdmins <= 1) {
            return 'No se puede quitar el último administrador del grupo.';
        }

        return null;
    }
}
