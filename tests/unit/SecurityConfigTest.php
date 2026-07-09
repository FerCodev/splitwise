<?php

use Config\Security;
use Config\Cookie as CookieConfig;
use Config\App as AppConfig;
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

    public function testAuthenticatedSessionCookieIsScopedToApp(): void
    {
        $session = new SessionConfig();
        $cookie = new CookieConfig();
        $basePath = parse_url(config(AppConfig::class)->baseURL, PHP_URL_PATH);
        $expectedPath = is_string($basePath) && $basePath !== '' && $basePath !== '/'
            ? rtrim($basePath, '/') . '/'
            : '/';

        $this->assertSame('gastito_session', $session->cookieName);
        $this->assertSame($expectedPath, $cookie->path);
    }

    public function testSessionRegenerationAvoidsMobileParallelRequestLogout(): void
    {
        $config = new SessionConfig();

        $this->assertSame(DAY, $config->timeToUpdate);
        $this->assertFalse($config->regenerateDestroy);
    }
}
