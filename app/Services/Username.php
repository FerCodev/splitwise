<?php

namespace App\Services;

use App\Models\User;

final class Username
{
    public const RESERVED = ['admin', 'api', 'amigos', 'dashboard', 'gastito', 'login', 'logout', 'perfil', 'soporte', 'support', 'usuarios'];

    public static function normalize(?string $value): string
    {
        return strtolower(ltrim(trim((string) $value), '@'));
    }

    public static function error(?string $value, ?int $exceptUserId = null): ?string
    {
        $username = self::normalize($value);
        if (!preg_match('/^[a-z0-9][a-z0-9._]{1,28}[a-z0-9]$/', $username)) {
            return 'El nombre de usuario debe tener entre 3 y 30 caracteres y usar solo letras min&uacute;sculas, n&uacute;meros, punto o guion bajo.';
        }
        if (in_array($username, self::RESERVED, true)) return 'Ese nombre de usuario est&aacute; reservado.';
        $query = (new User())->where('username', $username);
        if ($exceptUserId !== null) $query->where('id !=', $exceptUserId);
        return $query->first() ? 'Ese nombre de usuario ya est&aacute; en uso.' : null;
    }
}
