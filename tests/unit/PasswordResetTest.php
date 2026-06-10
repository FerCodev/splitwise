<?php

use App\Models\PasswordReset;

final class PasswordResetTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testTokenPlanoTiene64Caracteres(): void
    {
        $token = PasswordReset::generarTokenPlano();
        $this->assertSame(64, strlen($token));
    }

    public function testTokenPlanoEsHexadecimal(): void
    {
        $token = PasswordReset::generarTokenPlano();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testTokenPlanoEsAleatorio(): void
    {
        $t1 = PasswordReset::generarTokenPlano();
        $t2 = PasswordReset::generarTokenPlano();
        $this->assertNotSame($t1, $t2);
    }

    public function testHashearTokenDevuelveSha256(): void
    {
        $token = 'abc123';
        $hash = PasswordReset::hashearToken($token);
        $this->assertSame(64, strlen($hash));
        $this->assertSame(hash('sha256', $token), $hash);
    }

    public function testMismoTokenSiempreMismoHash(): void
    {
        $token = 'test-token-123';
        $h1 = PasswordReset::hashearToken($token);
        $h2 = PasswordReset::hashearToken($token);
        $this->assertSame($h1, $h2);
    }

    public function testTokensDistintosTienenDistintoHash(): void
    {
        $h1 = PasswordReset::hashearToken('token-a');
        $h2 = PasswordReset::hashearToken('token-b');
        $this->assertNotSame($h1, $h2);
    }

    public function testTokenNoExpirado(): void
    {
        $futuro = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $this->assertFalse(PasswordReset::estaExpirado($futuro));
    }

    public function testTokenExpirado(): void
    {
        $pasado = date('Y-m-d H:i:s', strtotime('-1 minute'));
        $this->assertTrue(PasswordReset::estaExpirado($pasado));
    }

    public function testTokenExpiradoEdgeCase(): void
    {
        $exactamenteAgora = date('Y-m-d H:i:s');
        $this->assertTrue(PasswordReset::estaExpirado($exactamenteAgora));
    }

    public function testTokenExpiradoHaceMucho(): void
    {
        $this->assertTrue(PasswordReset::estaExpirado('2020-01-01 00:00:00'));
    }

    public function testTokenFuturoLejanoNoExpirado(): void
    {
        $this->assertFalse(PasswordReset::estaExpirado('2099-12-31 23:59:59'));
    }

    public function testClasePasswordResetExiste(): void
    {
        $this->assertTrue(class_exists(PasswordReset::class));
    }

    public function testMetodosEstaticosExisten(): void
    {
        $this->assertTrue(method_exists(PasswordReset::class, 'generarTokenPlano'));
        $this->assertTrue(method_exists(PasswordReset::class, 'hashearToken'));
        $this->assertTrue(method_exists(PasswordReset::class, 'estaExpirado'));
        $this->assertTrue(method_exists(PasswordReset::class, 'generarToken'));
        $this->assertTrue(method_exists(PasswordReset::class, 'validarToken'));
        $this->assertTrue(method_exists(PasswordReset::class, 'marcarUsado'));
        $this->assertTrue(method_exists(PasswordReset::class, 'smtpConfigurado'));
        $this->assertTrue(method_exists(PasswordReset::class, 'enviarEmail'));
    }

    // ---------------------------------------------------------------
    // smtpConfigurado
    // ---------------------------------------------------------------

    public function testMetodoSmtpConfiguradoExiste(): void
    {
        $this->assertTrue(method_exists(PasswordReset::class, 'smtpConfigurado'));
    }

    public function testMetodoEnviarEmailExiste(): void
    {
        $this->assertTrue(method_exists(PasswordReset::class, 'enviarEmail'));
    }

    /**
     * El controlador solo muestra dev_reset_link cuando ENVIRONMENT === 'development'.
     * CI4 testing usa ENVIRONMENT='testing', por lo que el link jamas se renderiza
     * en tests. Se verifica la existencia del metodo como prueba de estructura.
     */
    public function testDevResetLinkEstructura(): void
    {
        $this->assertTrue(method_exists(\App\Controllers\PasswordResetController::class, 'enviarEnlace'));
    }
}
