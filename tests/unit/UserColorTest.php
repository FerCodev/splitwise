<?php

use App\Services\UserColor;

/**
 * Tests del servicio UserColor: paleta, reservados, validacion,
 * prioridad de resolucion, resolveMap.
 *
 * Toda la logica aca probada es pura (sin DB).
 */
final class UserColorTest extends \CodeIgniter\Test\CIUnitTestCase
{
    // ---- paleta y reservas ----

    public function testPaletteTieneAlMenosOchoColores(): void
    {
        $this->assertGreaterThanOrEqual(8, count(UserColor::PALETTE));
        $this->assertGreaterThanOrEqual(3, count(UserColor::RESERVED));
    }

    public function testPaletaNoIncluyeVerdeReservado(): void
    {
        $this->assertArrayNotHasKey('green', UserColor::PALETTE);
        $this->assertArrayNotHasKey('verde', UserColor::PALETTE);
    }

    public function testPaletaNoIncluyeRojoReservado(): void
    {
        $this->assertArrayNotHasKey('red', UserColor::PALETTE);
        $this->assertArrayNotHasKey('orange', UserColor::PALETTE);
        $this->assertArrayNotHasKey('rojo', UserColor::PALETTE);
    }

    public function testReservadosNoSonElegibles(): void
    {
        $this->assertTrue(UserColor::isReserved('payment'));
        $this->assertTrue(UserColor::isReserved('debt'));
        $this->assertTrue(UserColor::isReserved('system'));
        $this->assertFalse(UserColor::isValidKey('payment'));
        $this->assertFalse(UserColor::isValidKey('debt'));
        $this->assertFalse(UserColor::isValidKey('system'));
    }

    public function testColoresDePaletaSonValidos(): void
    {
        foreach (UserColor::PALETTE as $key => $info) {
            $this->assertTrue(UserColor::isValidKey($key), "{$key} deberia ser valido");
            $this->assertSame($key, (string) $key);
            $this->assertArrayHasKey('bg', $info);
            $this->assertArrayHasKey('border', $info);
            $this->assertArrayHasKey('solid', $info);
            $this->assertArrayHasKey('text', $info);
            $this->assertArrayHasKey('label', $info);
        }
    }

    // ---- validacion ----

    public function testClavesInvalidas(): void
    {
        $this->assertFalse(UserColor::isValidKey(null));
        $this->assertFalse(UserColor::isValidKey(''));
        $this->assertFalse(UserColor::isValidKey('payment'));
        $this->assertFalse(UserColor::isValidKey('not-a-color'));
        $this->assertFalse(UserColor::isValidKey('GREEN'));
    }

    public function testAutoEsValidoParaResolverPeroNoParaPaleta(): void
    {
        $this->assertFalse(UserColor::isValidKey(UserColor::DEFAULT_KEY));
        $this->assertNull(UserColor::get(UserColor::DEFAULT_KEY));
    }

    public function testSanitizeInputAceptaClavesDePaleta(): void
    {
        foreach (UserColor::paletteKeys() as $key) {
            $this->assertSame($key, UserColor::sanitizeInput($key));
        }
    }

    public function testSanitizeInputAceptaAuto(): void
    {
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::sanitizeInput('auto'));
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::sanitizeInput('  auto  '));
    }

    public function testSanitizeInputRechazaReservados(): void
    {
        $this->assertNull(UserColor::sanitizeInput('payment'));
        $this->assertNull(UserColor::sanitizeInput('debt'));
        $this->assertNull(UserColor::sanitizeInput('system'));
    }

    public function testSanitizeInputRechazaBasura(): void
    {
        $this->assertNull(UserColor::sanitizeInput(null));
        $this->assertNull(UserColor::sanitizeInput(''));
        $this->assertNull(UserColor::sanitizeInput('   '));
        $this->assertNull(UserColor::sanitizeInput('not-a-color'));
        $this->assertNull(UserColor::sanitizeInput('<script>'));
    }

    // ---- resolucion: prioridad override > global > auto ----

    public function testOverrideGanaSobreGlobal(): void
    {
        $this->assertSame('violet', UserColor::resolve('violet', 'amber'));
        $this->assertSame('teal', UserColor::resolve('teal', 'pink'));
    }

    public function testSiNoHayOverrideUsaGlobal(): void
    {
        $this->assertSame('amber', UserColor::resolve(null, 'amber'));
        $this->assertSame('lime', UserColor::resolve('', 'lime'));
    }

    public function testSiNoHayNadaDevuelveAuto(): void
    {
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::resolve(null, null));
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::resolve('', ''));
    }

    public function testOverrideReservadoSeIgnoraYCaeAlGlobal(): void
    {
        // Si alguien coloco un color reservado en la DB, treat as invalid
        // y caer al global. Asi nunca un reservado termina en pantalla
        // de gastos personalizables.
        $this->assertSame('amber', UserColor::resolve('payment', 'amber'));
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::resolve('debt', null));
    }

    public function testGlobalReservadoSeIgnoraYCaeAAuto(): void
    {
        $this->assertSame(UserColor::DEFAULT_KEY, UserColor::resolve(null, 'system'));
        $this->assertSame('violet', UserColor::resolve('violet', 'payment'));
    }

    // ---- resolveMap: union de claves y prioridad por entrada ----

    public function testResolveMapUneClavesDeOverridesYGlobals(): void
    {
        $overrides = [1 => 'violet'];
        $globals   = [2 => 'amber', 3 => 'lime'];
        $out = UserColor::resolveMap($overrides, $globals);
        $this->assertSame('violet', $out[1]);
        $this->assertSame('amber',  $out[2]);
        $this->assertSame('lime',   $out[3]);
    }

    public function testResolveMapPriorizaOverrideSobreGlobalEnMismoTarget(): void
    {
        $overrides = [1 => 'teal'];
        $globals   = [1 => 'amber'];
        $this->assertSame('teal', UserColor::resolveMap($overrides, $globals)[1]);
    }

    public function testResolveMapConMapasVaciosDevuelveVacio(): void
    {
        $this->assertSame([], UserColor::resolveMap([], []));
    }

    public function testResolveMapSoloOverrides(): void
    {
        $out = UserColor::resolveMap([1 => 'violet', 2 => 'lime'], []);
        $this->assertSame('violet', $out[1]);
        $this->assertSame('lime',   $out[2]);
    }

    public function testResolveMapSoloGlobals(): void
    {
        $out = UserColor::resolveMap([], [1 => 'violet', 2 => 'lime']);
        $this->assertSame('violet', $out[1]);
        $this->assertSame('lime',   $out[2]);
    }
}
