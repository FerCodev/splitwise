<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminUsersIndexViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        service('renderer')->resetData();
    }

    public function testMobileUserCardsAreClickableWithoutEditButton(): void
    {
        $html = view('usuarios/index', [
            'users' => [[
                'id' => 7,
                'name' => 'Ana Prueba',
                'username' => 'anaprueba',
                'email' => 'ana@example.com',
                'role' => 'user',
                'created_at' => '2026-07-06 10:00:00',
                'avatar_filename' => null,
                'avatar_updated_at' => null,
            ]],
        ]);

        $this->assertStringContainsString('class="card border-0 shadow-sm user-card user-card-link"', $html);
        $this->assertStringContainsString('/usuarios/7/editar"', $html);
        $this->assertStringContainsString('aria-label="Editar usuario Ana&#x20;Prueba"', $html);
        $this->assertStringContainsString('class="user-avatar"', $html);
        $this->assertStringNotContainsString('user-card-action', $html);
    }
}
