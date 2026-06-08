<?php

/**
 * @internal
 */
final class CsrfErrorMessageTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testSpanishCsrfErrorMessageUsesCustomMessage(): void
    {
        $message = lang('Security.disallowedAction');

        $this->assertStringContainsString('sesi\u00f3n', $message);
    }

    public function testSpanishCsrfMessageIsNotEmpty(): void
    {
        $message = lang('Security.disallowedAction');

        $this->assertNotEmpty($message);
    }
}