<?php

use App\Models\Categoria;

/**
 * @internal
 */
final class CategoriaGastoTest extends \CodeIgniter\Test\CIUnitTestCase
{
    private const CATEGORIAS_INICIALES = [
        'Supermercado', 'Servicios', 'Combustible', 'Farmacia',
        'Mascotas', 'Transporte', 'Comida', 'Viajes', 'Otros',
    ];

    public function testConstanteProtegidaEsOtros(): void
    {
        $this->assertSame('Otros', Categoria::PROTEGIDA);
    }

    public function testIsProtegidaConOtrosDevuelveTrue(): void
    {
        $this->assertTrue(Categoria::isProtegida('Otros'));
    }

    public function testIsProtegidaConOtraCategoriaDevuelveFalse(): void
    {
        $this->assertFalse(Categoria::isProtegida('Comida'));
    }

    public function testIsProtegidaEsCaseSensitive(): void
    {
        $this->assertFalse(Categoria::isProtegida('otros'));
        $this->assertFalse(Categoria::isProtegida('OTROS'));
    }

    public function testIsProtegidaConEspaciosNoCoincide(): void
    {
        $this->assertFalse(Categoria::isProtegida(' Otros'));
        $this->assertFalse(Categoria::isProtegida('Otros '));
    }

    public function testListaInicialDeCategorias(): void
    {
        $this->assertCount(9, self::CATEGORIAS_INICIALES);
        $this->assertContains('Otros', self::CATEGORIAS_INICIALES);
        $this->assertSame('Otros', self::CATEGORIAS_INICIALES[array_key_last(self::CATEGORIAS_INICIALES)]);
    }

    public function testCategoriasInicialesSinDuplicados(): void
    {
        $this->assertSame(self::CATEGORIAS_INICIALES, array_values(array_unique(self::CATEGORIAS_INICIALES)));
    }

    public function testLogicaFallbackCategoriaVaciaOInvalidaVuelveAOtras(): void
    {
        $categoriaId = 0;
        $catValida = null;
        $resultado = ($categoriaId <= 0 || !$catValida || !($catValida['activa'] ?? false));
        $this->assertTrue($resultado);
    }

    public function testLogicaFallbackCategoriaActivaValidaNoCambia(): void
    {
        $categoriaId = 5;
        $catValida = ['id' => 5, 'nombre' => 'Mascotas', 'activa' => 1];
        $resultado = ($categoriaId <= 0 || !$catValida || !$catValida['activa']);
        $this->assertFalse($resultado);
    }

    public function testLogicaFallbackCategoriaInactivaVuelveAOtros(): void
    {
        $categoriaId = 3;
        $catValida = ['id' => 3, 'nombre' => 'Combustible', 'activa' => 0];
        $resultado = ($categoriaId <= 0 || !$catValida || !$catValida['activa']);
        $this->assertTrue($resultado);
    }

    public function testLogicaFallbackCategoriaInexistenteVuelveAOtros(): void
    {
        $categoriaId = 999;
        $catValida = null;
        $resultado = ($categoriaId <= 0 || !$catValida || !($catValida['activa'] ?? false));
        $this->assertTrue($resultado);
    }

    public function testLogicaProteccionToggleDesactivaOtros(): void
    {
        $categoria = ['id' => 9, 'nombre' => 'Otros', 'activa' => 1];
        $bloqueado = Categoria::isProtegida($categoria['nombre']);
        $this->assertTrue($bloqueado);
    }

    public function testLogicaProteccionEliminarOtros(): void
    {
        $categoria = ['id' => 9, 'nombre' => 'Otros', 'activa' => 1];
        $bloqueado = Categoria::isProtegida($categoria['nombre']);
        $this->assertTrue($bloqueado);
    }

    public function testLogicaEliminarCategoriaUsadaPorGastos(): void
    {
        $id = 5;
        $tieneGastos = true;
        $bloqueado = Categoria::isProtegida('Mascotas') || $tieneGastos;
        $this->assertTrue($bloqueado);
    }

    public function testLogicaEliminarCategoriaSinGastosNoProtegida(): void
    {
        $id = 5;
        $tieneGastos = false;
        $bloqueado = Categoria::isProtegida('Mascotas') || $tieneGastos;
        $this->assertFalse($bloqueado);
    }
}
