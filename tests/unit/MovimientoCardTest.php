<?php

use CodeIgniter\Test\CIUnitTestCase;

final class MovimientoCardTest extends CIUnitTestCase
{
    public function testIgnoraGrupoNoTextualSinRomperLaVista(): void
    {
        $html = view('components/cards/movimiento', [
            'tipo' => 'gasto',
            'descripcion' => 'Supermercado',
            'monto' => 1500,
            'fecha' => '2026-06-29',
            'persona' => 'Fernando',
            'grupo' => ['id' => 1, 'nombre' => 'Junio'],
            'url' => null,
        ]);

        $this->assertStringContainsString('Supermercado', $html);
        $this->assertStringNotContainsString('Grupo:', $html);
    }

    public function testRenderizaGrupoTextual(): void
    {
        $html = view('components/cards/movimiento', [
            'tipo' => 'gasto',
            'descripcion' => 'Supermercado',
            'monto' => 1500,
            'fecha' => '2026-06-29',
            'persona' => 'Fernando',
            'grupo' => 'Junio',
            'participantes' => 2,
            'url' => null,
        ]);

        $this->assertStringContainsString('Grupo: Junio', $html);
        $this->assertStringContainsString('2 part.', $html);
    }
}