<?php

use Config\Security;

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
}
