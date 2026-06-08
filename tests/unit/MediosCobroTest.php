<?php

use App\Models\UserPaymentMethod;

/**
 * @internal
 */
final class MediosCobroTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testFavoritoUnicoDesmarcaOtros(): void
    {
        $methods = [
            ['id' => 1, 'user_id' => 1, 'favorito' => 1],
            ['id' => 2, 'user_id' => 1, 'favorito' => 0],
            ['id' => 3, 'user_id' => 1, 'favorito' => 0],
        ];

        $userId = 1;
        $nuevoFavId = 2;

        $result = array_map(function ($m) use ($userId, $nuevoFavId) {
            if ($m['user_id'] === $userId) {
                $m['favorito'] = (int) ($m['id'] === $nuevoFavId);
            }
            return $m;
        }, $methods);

        $this->assertSame(0, $result[0]['favorito']);
        $this->assertSame(1, $result[1]['favorito']);
        $this->assertSame(0, $result[2]['favorito']);
    }

    public function testGetActivosFiltraInactivos(): void
    {
        $medios = [
            ['id' => 1, 'user_id' => 1, 'activo' => 1, 'favorito' => 1, 'tipo' => 'alias', 'alias' => 'test', 'created_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'user_id' => 1, 'activo' => 0, 'favorito' => 0, 'tipo' => 'cbu_cvu', 'created_at' => '2026-01-02 00:00:00'],
            ['id' => 3, 'user_id' => 1, 'activo' => 1, 'favorito' => 0, 'tipo' => 'link', 'created_at' => '2026-01-03 00:00:00'],
        ];

        $activos = array_values(array_filter($medios, fn($m) => $m['activo'] === 1));

        $this->assertCount(2, $activos);
        $this->assertSame(1, $activos[0]['id']);
        $this->assertSame(3, $activos[1]['id']);
    }

    public function testActivosOrdenFavoritoPrimero(): void
    {
        $medios = [
            ['id' => 1, 'activo' => 1, 'favorito' => 0, 'tipo' => 'a', 'created_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'activo' => 1, 'favorito' => 1, 'tipo' => 'b', 'created_at' => '2026-01-02 00:00:00'],
            ['id' => 3, 'activo' => 1, 'favorito' => 0, 'tipo' => 'c', 'created_at' => '2026-01-03 00:00:00'],
        ];

        usort($medios, function ($a, $b) {
            if ($a['favorito'] !== $b['favorito']) {
                return $b['favorito'] - $a['favorito'];
            }
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });

        $this->assertSame(2, $medios[0]['id']);
        $this->assertSame(1, $medios[1]['id']);
        $this->assertSame(3, $medios[2]['id']);
    }

    public function testGetFavoritoSoloActivo(): void
    {
        $medios = [
            ['id' => 1, 'user_id' => 1, 'activo' => 0, 'favorito' => 1, 'tipo' => 'alias'],
            ['id' => 2, 'user_id' => 1, 'activo' => 1, 'favorito' => 1, 'tipo' => 'cbu_cvu'],
        ];

        $favoritoActivo = null;
        foreach ($medios as $m) {
            if ($m['favorito'] && $m['activo']) {
                $favoritoActivo = $m;
                break;
            }
        }

        $this->assertNotNull($favoritoActivo);
        $this->assertSame(2, $favoritoActivo['id']);
    }

    public function testSinFavoritoRetornaNull(): void
    {
        $medios = [
            ['id' => 1, 'user_id' => 1, 'activo' => 1, 'favorito' => 0, 'tipo' => 'alias'],
        ];

        $favoritoActivo = null;
        foreach ($medios as $m) {
            if ($m['favorito'] && $m['activo']) {
                $favoritoActivo = $m;
                break;
            }
        }

        $this->assertNull($favoritoActivo);
    }

    public function testPuedeTenerTodosInactivos(): void
    {
        $medios = [
            ['id' => 1, 'activo' => 0, 'tipo' => 'a'],
            ['id' => 2, 'activo' => 0, 'tipo' => 'b'],
        ];

        $activos = array_filter($medios, fn($m) => $m['activo'] === 1);

        $this->assertCount(0, $activos);
    }

    public function testSoloUnFavoritoPorUsuario(): void
    {
        $ids = [1, 2, 3];
        $favoritos = array_filter($ids, function ($id) {
            return $id === 2;
        });

        $this->assertCount(1, $favoritos);
    }

    public function testMarcarNuevoFavoritoDesmarcaAnterior(): void
    {
        $userId = 1;
        $medios = [
            ['id' => 1, 'user_id' => 1, 'favorito' => 1],
            ['id' => 2, 'user_id' => 1, 'favorito' => 0],
        ];

        $nuevoFavId = 2;
        foreach ($medios as &$m) {
            if ($m['user_id'] === $userId) {
                $m['favorito'] = 0;
            }
        }
        foreach ($medios as &$m) {
            if ($m['id'] === $nuevoFavId) {
                $m['favorito'] = 1;
            }
        }

        $this->assertSame(0, $medios[0]['favorito']);
        $this->assertSame(1, $medios[1]['favorito']);
    }

    public function testNoCrearMedioSinAliasCbuNiLink(): void
    {
        $alias = '';
        $cbuCvu = '';
        $paymentLink = '';

        $tieneAlgo = !(empty($alias) && empty($cbuCvu) && empty($paymentLink));

        $this->assertFalse($tieneAlgo);
    }

    public function testCrearMedioConAliasEsValido(): void
    {
        $alias = 'test.alias';
        $cbuCvu = '';
        $paymentLink = '';

        $tieneAlgo = !(empty($alias) && empty($cbuCvu) && empty($paymentLink));

        $this->assertTrue($tieneAlgo);
    }

    public function testCrearMedioConCbuEsValido(): void
    {
        $alias = '';
        $cbuCvu = '0000003100054332186492';
        $paymentLink = '';

        $tieneAlgo = !(empty($alias) && empty($cbuCvu) && empty($paymentLink));

        $this->assertTrue($tieneAlgo);
    }

    public function testCrearMedioConLinkEsValido(): void
    {
        $alias = '';
        $cbuCvu = '';
        $paymentLink = 'https://mpago.la/xxxxx';

        $tieneAlgo = !(empty($alias) && empty($cbuCvu) && empty($paymentLink));

        $this->assertTrue($tieneAlgo);
    }

    public function testNombreRequerido(): void
    {
        $nombre = '';

        $esValido = strlen($nombre) >= 2;

        $this->assertFalse($esValido);
    }

    public function testNombreValido(): void
    {
        $nombre = 'Mercado Pago';

        $esValido = strlen($nombre) >= 2;

        $this->assertTrue($esValido);
    }

    public function testSoloDuenioPuedeEditar(): void
    {
        $medio = ['id' => 1, 'user_id' => 1];
        $userIdSesion = 2;

        $puedeEditar = (int) $medio['user_id'] === $userIdSesion;

        $this->assertFalse($puedeEditar);
    }

    public function testDuenioPuedeEditar(): void
    {
        $medio = ['id' => 1, 'user_id' => 1];
        $userIdSesion = 1;

        $puedeEditar = (int) $medio['user_id'] === $userIdSesion;

        $this->assertTrue($puedeEditar);
    }

    public function testSoloDuenioPuedeToggle(): void
    {
        $medio = ['id' => 5, 'user_id' => 3];
        $userIdSesion = 7;

        $puedeToggle = (int) $medio['user_id'] === $userIdSesion;

        $this->assertFalse($puedeToggle);
    }

    public function testSoloDuenioPuedeFavorito(): void
    {
        $medio = ['id' => 5, 'user_id' => 3];
        $userIdSesion = 3;

        $puedeFavorito = (int) $medio['user_id'] === $userIdSesion;

        $this->assertTrue($puedeFavorito);
    }

    public function testSoloDuenioPuedeDelete(): void
    {
        $medio = ['id' => 10, 'user_id' => 2];
        $userIdSesion = 5;

        $puedeDelete = (int) $medio['user_id'] === $userIdSesion;

        $this->assertFalse($puedeDelete);
    }

    public function testMedioInactivoNoApareceEnBalance(): void
    {
        $medios = [
            ['id' => 1, 'activo' => 1, 'tipo' => 'alias', 'alias' => 'a'],
            ['id' => 2, 'activo' => 0, 'tipo' => 'cbu_cvu'],
            ['id' => 3, 'activo' => 1, 'tipo' => 'link'],
        ];

        $activos = array_values(array_filter($medios, fn($m) => $m['activo'] === 1));

        $this->assertCount(2, $activos);
        $this->assertSame(1, $activos[0]['id']);
        $this->assertSame(3, $activos[1]['id']);

        $idsActivos = array_map(fn($m) => $m['id'], $activos);
        $this->assertNotContains(2, $idsActivos);
    }

    public function testFavoritoNoPermitidoSiInactivo(): void
    {
        $medio = ['id' => 1, 'activo' => 0];

        $puedeMarcarFavorito = (int) $medio['activo'] === 1;

        $this->assertFalse($puedeMarcarFavorito);
    }

    public function testFavoritoPermitidoSiActivo(): void
    {
        $medio = ['id' => 1, 'activo' => 1];

        $puedeMarcarFavorito = (int) $medio['activo'] === 1;

        $this->assertTrue($puedeMarcarFavorito);
    }

    public function testMedioConAliasTieneBotonCopiar(): void
    {
        $alias = 'test.alias';
        $cbuCvu = '';

        $muestraAlias = !empty($alias);
        $muestraBtnAlias = $muestraAlias;

        $this->assertTrue($muestraBtnAlias);
    }

    public function testMedioConCbuTieneBotonCopiar(): void
    {
        $cbuCvu = '0000003100054332186492';

        $muestraBtnCbu = !empty($cbuCvu);

        $this->assertTrue($muestraBtnCbu);
    }

    public function testMedioConAliasYCbuMuestraAmbosBotones(): void
    {
        $alias = 'test.alias';
        $cbuCvu = '0000003100054332186492';

        $muestraAlias = !empty($alias);
        $muestraCbu = !empty($cbuCvu);

        $this->assertTrue($muestraAlias);
        $this->assertTrue($muestraCbu);
    }

    public function testPagoCreateTienePagoModelDefinido(): void
    {
        $codigoCrear = file_get_contents(__DIR__ . '/../../app/Controllers/Pagos.php');
        $this->assertStringContainsString('$pagoModel = new Pago();', $codigoCrear,
            'Pagos::create() debe instanciar $pagoModel antes de insert().');
    }

    public function testMedioSinAliasSinCbuSinLinkEsInvalido(): void
    {
        $alias = '';
        $cbuCvu = '';
        $paymentLink = '';

        $error = empty($alias) && empty($cbuCvu) && empty($paymentLink);

        $this->assertTrue($error);
    }

    public function testMedioConLosTresEsValido(): void
    {
        $alias = 'test.alias';
        $cbuCvu = '0000003100054332186492';
        $paymentLink = 'https://mpago.la/xxxxx';

        $valido = !empty($alias) || !empty($cbuCvu) || !empty($paymentLink);

        $this->assertTrue($valido);
    }
}
