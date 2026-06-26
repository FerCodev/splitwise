<?php

namespace App\Services;

/**
 * Servicio unico para la paleta de colores personalizables por usuario.
 *
 * Centraliza:
 *   - paleta permitida (la unica que el usuario puede elegir)
 *   - colores reservados (pagos, deudas, sistema) que NO son elegibles
 *   - validacion de color
 *   - resolucion efectiva (override > global > auto)
 *   - decision de submit del endpoint de grupos (reset/set/error)
 *
 * Los metodos son estaticos para que sean directamente testeables sin DB
 * ni estado compartido. Toda la logica pura vive aca; el acceso a datos
 * (overrides, color global) lo hace el llamador.
 */
class UserColor
{
    public const DEFAULT_KEY = 'auto';

    public const SUBMIT_RESET  = 'reset';
    public const SUBMIT_SET    = 'set';
    public const SUBMIT_ERROR  = 'error';
    public const REASON_EMPTY  = 'empty';
    public const REASON_INVALID = 'invalid';

    /**
     * Paleta cerrada, segura y con buen contraste. NO incluye verde
     * (reservado para pagos), rojo/naranja (reservado para deudas) ni
     * el celeste claro del sistema. Todas las claves son en kebab-case
     * para sobrevivir un form-urlencoded sin escapes.
     *
     * Cada entrada expone:
     *   - bg:     fondo suave para tarjetas
     *   - border: acento lateral / borde
     *   - solid:  color solido para monto / badge
     *   - text:   color de texto sobre `bg`
     *   - label:  etiqueta visible al usuario
     */
    public const PALETTE = [
        'amber' => [
            'bg'     => '#fef3c7',
            'border' => '#f59e0b',
            'solid'  => '#b45309',
            'text'   => '#78350f',
            'label'  => 'Ambar',
        ],
        'violet' => [
            'bg'     => '#ede9fe',
            'border' => '#7c3aed',
            'solid'  => '#6d28d9',
            'text'   => '#4c1d95',
            'label'  => 'Violeta',
        ],
        'teal' => [
            'bg'     => '#ccfbf1',
            'border' => '#14b8a6',
            'solid'  => '#0f766e',
            'text'   => '#134e4a',
            'label'  => 'Turquesa',
        ],
        'slate' => [
            'bg'     => '#e2e8f0',
            'border' => '#64748b',
            'solid'  => '#475569',
            'text'   => '#1e293b',
            'label'  => 'Gris azulado',
        ],
        'pink' => [
            'bg'     => '#fce7f3',
            'border' => '#ec4899',
            'solid'  => '#be185d',
            'text'   => '#831843',
            'label'  => 'Rosa',
        ],
        'indigo' => [
            'bg'     => '#e0e7ff',
            'border' => '#4f46e5',
            'solid'  => '#4338ca',
            'text'   => '#312e81',
            'label'  => 'Indigo',
        ],
        'lime' => [
            'bg'     => '#ecfccb',
            'border' => '#84cc16',
            'solid'  => '#4d7c0f',
            'text'   => '#365314',
            'label'  => 'Lima',
        ],
        'fuchsia' => [
            'bg'     => '#fae8ff',
            'border' => '#d946ef',
            'solid'  => '#c026d3',
            'text'   => '#86198f',
            'label'  => 'Fucsia',
        ],
    ];

    /**
     * Colores fijos que el sistema reserva para pagos, deudas y gastos
     * sin pagador claro. NO son elegibles. Sirven para que el caller
     * sepa cuales pintar sin personalizacion.
     */
    public const RESERVED = [
        'payment' => [
            'bg'     => '#dff5ea',
            'border' => '#16a34a',
            'solid'  => '#15803d',
            'text'   => '#14532d',
        ],
        'debt' => [
            'bg'     => '#fef2f2',
            'border' => '#dc2626',
            'solid'  => '#b91c1c',
            'text'   => '#7f1d1d',
        ],
        'system' => [
            'bg'     => '#dbeafe',
            'border' => '#2563eb',
            'solid'  => '#1d4ed8',
            'text'   => '#1e3a8a',
        ],
    ];

    /**
     * Lista de claves elegibles. Util para iterar la UI del picker.
     *
     * @return string[]
     */
    public static function paletteKeys(): array
    {
        return array_keys(self::PALETTE);
    }

    /**
     * Indica si $key es una clave elegible de la paleta.
     */
    public static function isValidKey(?string $key): bool
    {
        return is_string($key) && isset(self::PALETTE[$key]);
    }

    /**
     * Indica si $key representa un color reservado del sistema.
     */
    public static function isReserved(?string $key): bool
    {
        return is_string($key) && isset(self::RESERVED[$key]);
    }

    /**
     * Devuelve la entrada de paleta para $key, o null si no es valida.
     * Acepta la clave magica 'auto' devolviendo null (quiere decir
     * "usa el color por defecto que es system o global del target").
     */
    public static function get(?string $key): ?array
    {
        if ($key === null || $key === self::DEFAULT_KEY) {
            return null;
        }
        return self::PALETTE[$key] ?? null;
    }

    /**
     * Resuelve el color efectivo para un target visto por un viewer en
     * un grupo, siguiendo la prioridad:
     *
     *   1) override especifico del viewer para ese target en ese grupo
     *   2) color global actual del target
     *   3) 'auto' (la UI lo renderizara con system)
     *
     * Metodo puro. El caller pasa el override y el color global como
     * escalares para mantener la logica testeable.
     */
    public static function resolve(?string $overrideKey, ?string $targetGlobalKey): string
    {
        if (self::isValidKey($overrideKey)) {
            return $overrideKey;
        }
        if (self::isValidKey($targetGlobalKey)) {
            return $targetGlobalKey;
        }
        return self::DEFAULT_KEY;
    }

    /**
     * Resuelve un mapa de targets a su color efectivo a partir de dos
     * mapas parciales: overrides y colors globales. La union de claves
     * determina el dominio. Si un target solo figura en uno de los dos
     * mapas, el otro se toma como null. Util para resolver N miembros
     * en una sola llamada sin repetir la logica de prioridad.
     *
     * @param array<int, string|null> $overrides  targetId => color override
     * @param array<int, string|null> $globals    targetId => color global
     * @return array<int, string>                 targetId => color efectivo
     */
    public static function resolveMap(array $overrides, array $globals): array
    {
        $out = [];
        $keys = array_unique(array_merge(array_keys($overrides), array_keys($globals)));
        foreach ($keys as $targetId) {
            $out[(int) $targetId] = self::resolve(
                $overrides[$targetId] ?? null,
                $globals[$targetId] ?? null
            );
        }
        return $out;
    }

    /**
     * Filtra una clave enviada por el usuario a un valor seguro de
     * paleta, o null si no es aceptable. Nunca devuelve claves
     * reservadas: un color reservado que llega por input es tratado
     * como invalido.
     */
    public static function sanitizeInput(?string $raw): ?string
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $raw = trim($raw);
        if ($raw === self::DEFAULT_KEY) {
            return self::DEFAULT_KEY;
        }
        if (self::isValidKey($raw)) {
            return $raw;
        }
        return null;
    }

    /**
     * Clasifica un submit del endpoint de color de grupo en una de tres
     * acciones: reset (borrar override), set (guardar override con la
     * clave devuelta), o error (no tocar nada).
     *
     * Reglas:
     *   - action='reset' (boton Global)              -> reset
     *   - color='auto' (input explicito)             -> reset
     *   - color vacio o solo whitespace              -> error empty
     *   - color en paleta                            -> set con esa clave
     *   - cualquier otra cosa (basura, reservado)   -> error invalid
     *
     * El controller usa este metodo para decidir si llama
     * clearOverride, setOverride, o devuelve un mensaje de error
     * sin tocar la DB.
     *
     * @return array{action: string, colorKey?: string, reason?: string}
     */
    public static function classifyOverrideSubmit(?string $action, ?string $rawColor): array
    {
        $action    = is_string($action) ? trim($action) : '';
        $rawColor  = is_string($rawColor) ? trim($rawColor) : '';

        if ($action === self::SUBMIT_RESET) {
            return ['action' => self::SUBMIT_RESET];
        }

        if ($rawColor === self::DEFAULT_KEY) {
            return ['action' => self::SUBMIT_RESET];
        }

        if ($rawColor === '') {
            return ['action' => self::SUBMIT_ERROR, 'reason' => self::REASON_EMPTY];
        }

        $color = self::sanitizeInput($rawColor);
        if ($color === null) {
            return ['action' => self::SUBMIT_ERROR, 'reason' => self::REASON_INVALID];
        }

        return ['action' => self::SUBMIT_SET, 'colorKey' => $color];
    }
}
