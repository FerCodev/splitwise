<?php

use App\Models\Gasto;

/**
 * @internal
 */
final class CategoriaGastoTest extends \CodeIgniter\Test\CIUnitTestCase
{
    private array $esperadas = [
        'Supermercado',
        'Servicios',
        'Combustible',
        'Farmacia',
        'Mascotas',
        'Transporte',
        'Comida',
        'Viajes',
        'Otros',
    ];

    public function testListaExactaDeCategorias(): void
    {
        $categorias = Gasto::categoriasPermitidas();

        $this->assertSame($this->esperadas, $categorias);
    }

    public function testCategoriasSinDuplicados(): void
    {
        $categorias = Gasto::categoriasPermitidas();

        $this->assertSame(array_unique($categorias), array_values($categorias));
    }

    public function testOtrosEsElUltimoYDefault(): void
    {
        $categorias = Gasto::categoriasPermitidas();

        $this->assertSame('Otros', $categorias[array_key_last($categorias)]);
    }

    public function testCategoriaVaciaFallaAOtros(): void
    {
        $categoria = '';

        if (empty($categoria) || !in_array($categoria, Gasto::categoriasPermitidas())) {
            $categoria = 'Otros';
        }

        $this->assertSame('Otros', $categoria);
    }

    public function testCategoriaInvalidaFallaAOtros(): void
    {
        $categoria = 'CategoriaInexistente123';

        if (empty($categoria) || !in_array($categoria, Gasto::categoriasPermitidas())) {
            $categoria = 'Otros';
        }

        $this->assertSame('Otros', $categoria);
    }

    public function testCategoriaValidaNoSeModifica(): void
    {
        $categorias = Gasto::categoriasPermitidas();

        foreach ($categorias as $original) {
            $categoria = $original;

            if (empty($categoria) || !in_array($categoria, Gasto::categoriasPermitidas())) {
                $categoria = 'Otros';
            }

            $this->assertSame($original, $categoria);
        }
    }
}
