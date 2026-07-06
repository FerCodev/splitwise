<?php

use Config\Security;
use Config\Session as SessionConfig;

/**
 * @internal
 */
final class SecurityConfigTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testCsrfProtectionIsEnabled(): void
    {
        $config = new Security();

        $this->assertSame('cookie', $config->csrfProtection);
        $this->assertSame('csrf_test_name', $config->tokenName);
    }

    public function testCsrfRedirectIsEnabled(): void
    {
        $config = new Security();

        $this->assertTrue($config->redirect, 'CSRF debe redirigir en vez de lanzar excepci' . "\xc3\xb3" . 'n.');
    }

    public function testCsrfExpiresIsReasonable(): void
    {
        $config = new Security();

        $this->assertGreaterThanOrEqual(7200, $config->expires);
    }

    public function testCsrfRegenerateIsEnabled(): void
    {
        $config = new Security();

        $this->assertTrue($config->regenerate);
    }

    public function testAuthenticatedSessionLastsThirtyDays(): void
    {
        $config = new SessionConfig();

        $this->assertSame(30 * DAY, $config->expiration);
    }
}
