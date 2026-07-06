<?php

/**
 * @internal
 */
final class ExpenseDeleteNavigationTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testDeleteFormSupportsInternalReturnContext(): void
    {
        $html = view('components/forms/delete_form', [
            'action' => base_url('gastos/10'),
            'formId' => 'delete-gasto-10',
            'hiddenFields' => ['return_to' => 'grupo'],
        ]);

        $this->assertStringContainsString('name="return_to"', $html);
        $this->assertStringContainsString('value="grupo"', $html);
    }

    public function testDeleteControllerMapsOnlyKnownInternalDestinations(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/Gastos.php');

        $this->assertStringContainsString("'grupo' => '/grupos/' . \$grupoId", $controller);
        $this->assertStringContainsString("'notificaciones' => '/notificaciones'", $controller);
        $this->assertStringContainsString("default => '/dashboard'", $controller);
    }

    public function testOpeningNotificationAddsReturnContext(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/Notificaciones.php');

        $this->assertStringContainsString('return_to=notificaciones', $controller);
    }
}
