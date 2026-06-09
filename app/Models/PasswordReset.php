<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token_hash', 'expires_at', 'used_at'];
    protected $useTimestamps = true;

    /**
     * Genera un token de recuperacion y lo guarda hasheado.
     * Invalida tokens anteriores del mismo usuario.
     *
     * @return string token plano (para construir el enlace)
     */
    public function generarToken(int $userId): string
    {
        $this->where('user_id', $userId)
             ->where('used_at IS NULL')
             ->set(['used_at' => date('Y-m-d H:i:s')])
             ->update();

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $this->insert([
            'user_id' => $userId,
            'token_hash' => $hash,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+60 minutes')),
        ]);

        return $token;
    }

    /**
     * Valida un token plano: existe, no expirado, no usado.
     * Retorna el registro o null.
     */
    public function validarToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $row = $this->where('token_hash', $hash)
                    ->where('used_at IS NULL')
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
        return $row;
    }

    /**
     * Marca un token como usado.
     */
    public function marcarUsado(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }

    // ---------------------------------------------------------------
    // Metodos puros para testing
    // ---------------------------------------------------------------

    /**
     * Genera token plano. Puro, testeable sin DB.
     */
    public static function generarTokenPlano(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hashea un token plano. Puro, testeable sin DB.
     */
    public static function hashearToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Verifica si un token esta expirado segun expires_at.
     * Puro, testeable sin DB.
     */
    public static function estaExpirado(string $expiresAt): bool
    {
        return strtotime($expiresAt) <= time();
    }
}
