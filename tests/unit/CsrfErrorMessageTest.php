<?php

/**
 * @internal
 */
final class CsrfErrorMessageTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testSpanishCsrfErrorMessageUsesCustomMessage(): void
    {
        $message = lang('Security.disallowedAction');

        $this->assertStringContainsString('sesi' . "\xc3\xb3" . 'n', $message);
    }

    public function testSpanishCsrfMessageContainsFormulario(): void
    {
        $message = lang('Security.disallowedAction');

        $this->assertStringContainsString('formulario', $message);
    }

    public function testSpanishCsrfMessageIsNotEmpty(): void
    {
        $message = lang('Security.disallowedAction');

        $this->assertNotEmpty($message);
    }
}