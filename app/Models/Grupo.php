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
            ->select('grupo_miembros.*, users.name, users.email, users.avatar_filename, users.avatar_updated_at' . $this->usernameSelectFragment())
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
            'gasto_create' => ['cerrado' => 'No se pueden crear gastitos en un grupo cerrado.', 'liquidado' => 'No se pueden crear gastitos en un grupo liquidado.'],
            'gasto_edit'   => ['cerrado' => 'No se pueden editar gastitos en un grupo cerrado.', 'liquidado' => 'No se pueden editar gastitos en un grupo liquidado.'],
            'gasto_delete' => ['cerrado' => 'No se pueden eliminar gastitos en un grupo cerrado.', 'liquidado' => 'No se pueden eliminar gastitos en un grupo liquidado.'],
            'pago_create'  => ['cerrado' => 'En un grupo cerrado solo se pueden registrar pagos para saldar deudas.', 'liquidado' => 'No se pueden registrar pagos en un grupo liquidado.'],
            'pago_edit'    => ['cerrado' => 'No se pueden editar pagos en un grupo cerrado.', 'liquidado' => 'No se pueden editar pagos en un grupo liquidado.'],
            'pago_delete'  => ['cerrado' => 'No se pueden eliminar pagos en un grupo cerrado.', 'liquidado' => 'No se pueden eliminar pagos en un grupo liquidado.'],
            'grupo_edit'     => ['cerrado' => 'No se puede editar un grupo cerrado.', 'liquidado' => 'No se puede editar un grupo liquidado.'],
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
     * reciente entre: ultimo gasto, ultimo pago (por created_at/updated_at,
     * no por fecha contable), y los timestamps del grupo.
     *
     * Usa subqueries para evitar multiplicacion de filas por JOIN cruzado.
     */
    public function getUltimaActividadByUser(int $userId): array
    {
        $sql = "SELECT g.id as grupo_id,
                       GREATEST(
                           COALESCE(
                               (SELECT MAX(GREATEST(
                                   COALESCE(gast.updated_at, '1970-01-01'),
                                   COALESCE(gast.created_at, '1970-01-01'),
                                   COALESCE(gast.fecha, '1970-01-01')
                               )) FROM gastos gast WHERE gast.grupo_id = g.id),
                               '1970-01-01'
                           ),
                           COALESCE(
                               (SELECT MAX(GREATEST(
                                   COALESCE(pag.updated_at, '1970-01-01'),
                                   COALESCE(pag.created_at, '1970-01-01'),
                                   COALESCE(pag.fecha, '1970-01-01')
                               )) FROM pagos pag WHERE pag.grupo_id = g.id),
                               '1970-01-01'
                           ),
                           COALESCE(g.updated_at, '1970-01-01'),
                           COALESCE(g.created_at, '1970-01-01')
                       ) as ultima_actividad
                  FROM grupos g
                  JOIN grupo_miembros gm ON gm.grupo_id = g.id AND gm.user_id = ?";

        $rows = $this->db->query($sql, [$userId])->getResultArray();
        $result = [];
        foreach ($rows as $r) {
            $result[(int) $r['grupo_id']] = $r['ultima_actividad'];
        }
        return $result;
    }

    /**
     * Retorna un array asociativo [grupo_id => ultimo_movimiento] con los
     * datos del ultimo movimiento (gasto o pago) de cada grupo del usuario.
     * Si un grupo no tiene movimientos, no aparece en el resultado.
     *
     * Cada entrada contiene: tipo, descripcion, monto, fecha.
     */
    public function getUltimoMovimientoByUser(int $userId): array
    {
        $sql = "SELECT grupo_id, tipo, descripcion, monto, fecha
                  FROM (
                      SELECT g.id as grupo_id, 'gasto' as tipo,
                             gast.descripcion, gast.monto, gast.fecha,
                             GREATEST(
                                 COALESCE(gast.updated_at, '1970-01-01'),
                                 COALESCE(gast.created_at, '1970-01-01'),
                                 COALESCE(gast.fecha, '1970-01-01')
                             ) as sort_date,
                             gast.id as mov_id
                        FROM grupos g
                        JOIN grupo_miembros gm ON gm.grupo_id = g.id AND gm.user_id = ?
                        JOIN gastos gast ON gast.grupo_id = g.id

                      UNION ALL

                      SELECT g.id, 'pago',
                             COALESCE(pag.descripcion, 'Pago'), pag.monto, pag.fecha,
                             GREATEST(
                                 COALESCE(pag.updated_at, '1970-01-01'),
                                 COALESCE(pag.created_at, '1970-01-01'),
                                 COALESCE(pag.fecha, '1970-01-01')
                             ),
                             pag.id
                        FROM grupos g
                        JOIN grupo_miembros gm ON gm.grupo_id = g.id AND gm.user_id = ?
                        JOIN pagos pag ON pag.grupo_id = g.id
                  ) m
                  ORDER BY grupo_id, sort_date DESC, mov_id DESC";

        $rows = $this->db->query($sql, [$userId, $userId])->getResultArray();

        $result = [];
        foreach ($rows as $r) {
            $gid = (int) $r['grupo_id'];
            if (!isset($result[$gid])) {
                $result[$gid] = [
                    'tipo' => $r['tipo'],
                    'descripcion' => $r['descripcion'],
                    'monto' => (float) $r['monto'],
                    'fecha' => $r['fecha'],
                ];
            }
        }
        return $result;
    }

    public function getUsuariosDisponibles(int $grupoId, int $viewerId): array
    {
        $sql = 'SELECT users.id, users.name, users.email, users.avatar_filename, users.avatar_updated_at' . $this->usernameSelectFragment(true) . '
                FROM users
                INNER JOIN friendships ON friendships.status = ? AND (
                    (friendships.user_low_id = ? AND friendships.user_high_id = users.id)
                    OR (friendships.user_high_id = ? AND friendships.user_low_id = users.id)
                )
                WHERE users.role != ? AND users.id NOT IN (
                    SELECT user_id FROM grupo_miembros WHERE grupo_id = ?
                ) ORDER BY name ASC';

        return $this->db->query($sql, ['accepted', $viewerId, $viewerId, 'admin', $grupoId])->getResultArray();
    }

    private function usernameSelectFragment(bool $withEmptyAlias = false): string
    {
        if ($this->db->fieldExists('username', 'users')) {
            return ', users.username';
        }

        return $withEmptyAlias ? ", '' AS username" : '';
    }

    // ---------------------------------------------------------------
    // Ordenamiento de grupos por actividad (puro, testeable sin DB)
    // ---------------------------------------------------------------

    /**
     * Ordena grupos: activos primero, luego por ultima_actividad DESC.
     * Metodo puro para testear sin DB.
     *
     * @param array $grupos Lista de grupos con claves 'estado' y 'ultima_actividad'
     * @return array Grupos ordenados
     */
    public static function sortGroupsByActivity(array $grupos): array
    {
        usort($grupos, function ($a, $b) {
            $ordenA = ($a['estado'] ?? 'activo') === 'activo' ? 0 : 1;
            $ordenB = ($b['estado'] ?? 'activo') === 'activo' ? 0 : 1;
            if ($ordenA !== $ordenB) {
                return $ordenA - $ordenB;
            }
            return strcmp($b['ultima_actividad'] ?? '1970-01-01', $a['ultima_actividad'] ?? '1970-01-01');
        });
        return $grupos;
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
