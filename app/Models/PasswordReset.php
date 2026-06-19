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

    /**
     * Envia un email usando SMTP configurado en .env.
     * Retorna true si se envio correctamente, false si no habia SMTP o fallo.
     */
    public static function enviarEmail(string $to, string $subject, string $message): bool
    {
        if (!self::smtpConfigurado()) {
            log_message('error', 'Intento de envio de email sin configuracion SMTP completa');
            return false;
        }

        try {
            $email = \Config\Services::email();
            $email->protocol = 'smtp';
            $email->SMTPHost = env('email.SMTPHost');
            $email->SMTPUser = env('email.SMTPUser');
            $email->SMTPPass = env('email.SMTPPass');
            $email->SMTPPort = (int) (env('email.SMTPPort') ?: 587);
            $email->SMTPCrypto = env('email.SMTPCrypto') ?: 'tls';
            $email->SMTPAuth = true;

            $email->setFrom(env('email.fromEmail'), env('email.fromName') ?: 'SplitWise');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($message);

            $result = $email->send();

            if (!$result) {
                log_message('error', 'Email no enviado a ' . $to . '. Debug: ' . print_r($email->getDebugMessage(), true));
            }

            return $result;
        } catch (\Throwable $e) {
            log_message('error', 'Excepcion al enviar email a ' . $to . ': ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------
    // Metodos puros para testing
    // ---------------------------------------------------------------

    /**
     * Indica si la configuracion SMTP esta completa en .env.
     * Requiere: protocol=smtp, SMTPHost, SMTPUser y SMTPPass no vacios.
     * Puro, testeable sin DB.
     */
    public static function smtpConfigurado(): bool
    {
        return env('email.protocol') === 'smtp'
            && !empty(env('email.SMTPHost'))
            && !empty(env('email.SMTPUser'))
            && !empty(env('email.SMTPPass'));
    }

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
