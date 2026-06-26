<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\UserColor;

/**
 * Override por (viewer, grupo, target). Solo guarda el color elegido
 * por el viewer; si no hay fila, se resuelve con el color global del
 * target. La tabla nunca guarda defaults para no duplicar informacion.
 */
class UserGroupColorOverride extends Model
{
    protected $table         = 'user_group_color_overrides';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['viewer_user_id', 'group_id', 'target_user_id', 'color'];
    protected $useTimestamps = true;

    /**
     * Devuelve el override crudo del viewer para un target en un grupo,
     * o null si no existe. NO valida que el color siga siendo elegible;
     * eso lo hace UserColor::resolve en tiempo de render.
     */
    public function getOverride(int $viewerId, int $groupId, int $targetId): ?string
    {
        $row = $this->where('viewer_user_id', $viewerId)
            ->where('group_id', $groupId)
            ->where('target_user_id', $targetId)
            ->first();

        return $row['color'] ?? null;
    }

    /**
     * Devuelve un mapa [target_user_id => color] con los overrides del
     * viewer en un grupo. Se usa para resolver todos los miembros en
     * una sola query.
     *
     * @return array<int, string>
     */
    public function getOverridesForGroup(int $viewerId, int $groupId): array
    {
        $rows = $this->select('target_user_id, color')
            ->where('viewer_user_id', $viewerId)
            ->where('group_id', $groupId)
            ->findAll();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['target_user_id']] = (string) $r['color'];
        }
        return $out;
    }

    /**
     * Inserta o actualiza el override. Solo acepta claves de la paleta
     * permitida: ni 'auto' ni un color reservado. Para volver al color
     * global del target el caller debe usar clearOverride().
     *
     * Un color invalido lanza excepcion para que el caller aborte la
     * operacion antes de tocar la DB.
     */
    public function setOverride(int $viewerId, int $groupId, int $targetId, string $color): void
    {
        if (! UserColor::isValidKey($color)) {
            throw new \InvalidArgumentException("Color no permitido: {$color}");
        }

        $existing = $this->where('viewer_user_id', $viewerId)
            ->where('group_id', $groupId)
            ->where('target_user_id', $targetId)
            ->first();

        if ($existing) {
            $this->update($existing['id'], ['color' => $color]);
            return;
        }

        $this->insert([
            'viewer_user_id' => $viewerId,
            'group_id'       => $groupId,
            'target_user_id' => $targetId,
            'color'          => $color,
        ]);
    }

    /**
     * Elimina el override, dejando que la resolucion vuelva al color
     * global del target. No falla si no existe la fila.
     */
    public function clearOverride(int $viewerId, int $groupId, int $targetId): void
    {
        $this->where('viewer_user_id', $viewerId)
            ->where('group_id', $groupId)
            ->where('target_user_id', $targetId)
            ->delete();
    }
}
