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

    // ---- classifyOverrideSubmit: logica de submit del endpoint de grupo ----

    public function testClassifyActionResetBorraOverride(): void
    {
        $d = UserColor::classifyOverrideSubmit('reset', 'amber');
        $this->assertSame(UserColor::SUBMIT_RESET, $d['action']);
        $this->assertArrayNotHasKey('colorKey', $d);

        // action=reset gana incluso si color es basura o vacio.
        $d = UserColor::classifyOverrideSubmit('reset', '');
        $this->assertSame(UserColor::SUBMIT_RESET, $d['action']);

        $d = UserColor::classifyOverrideSubmit('reset', 'payment');
        $this->assertSame(UserColor::SUBMIT_RESET, $d['action']);
    }

    public function testClassifyAutoResetBorraOverride(): void
    {
        // Input explicito 'auto' == volver al global, igual que el
        // boton Global. No debe llegar a setOverride.
        $d = UserColor::classifyOverrideSubmit(null, 'auto');
        $this->assertSame(UserColor::SUBMIT_RESET, $d['action']);

        $d = UserColor::classifyOverrideSubmit('', '  auto  ');
        $this->assertSame(UserColor::SUBMIT_RESET, $d['action']);
    }

    public function testClassifyColorValidoDevuelveSet(): void
    {
        $d = UserColor::classifyOverrideSubmit(null, 'amber');
        $this->assertSame(UserColor::SUBMIT_SET, $d['action']);
        $this->assertSame('amber', $d['colorKey']);

        $d = UserColor::classifyOverrideSubmit(null, '  violet  ');
        $this->assertSame(UserColor::SUBMIT_SET, $d['action']);
        $this->assertSame('violet', $d['colorKey']);
    }

    public function testClassifyVacioDevuelveErrorEmpty(): void
    {
        // Sin seleccion explicita. El controller NO debe llamar a
        // setOverride ni a clearOverride: ni setea ni borra.
        $d = UserColor::classifyOverrideSubmit(null, null);
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_EMPTY, $d['reason']);

        $d = UserColor::classifyOverrideSubmit('', '');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_EMPTY, $d['reason']);

        $d = UserColor::classifyOverrideSubmit(null, '   ');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_EMPTY, $d['reason']);
    }

    public function testClassifyColorInvalidoDevuelveErrorInvalid(): void
    {
        // Color basura o reservado: el controller NO debe borrar el
        // override; debe responder con error.
        $d = UserColor::classifyOverrideSubmit(null, 'payment');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_INVALID, $d['reason']);

        $d = UserColor::classifyOverrideSubmit(null, 'debt');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_INVALID, $d['reason']);

        $d = UserColor::classifyOverrideSubmit(null, 'system');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_INVALID, $d['reason']);

        $d = UserColor::classifyOverrideSubmit(null, 'not-a-color');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_INVALID, $d['reason']);

        $d = UserColor::classifyOverrideSubmit(null, '<script>');
        $this->assertSame(UserColor::SUBMIT_ERROR, $d['action']);
        $this->assertSame(UserColor::REASON_INVALID, $d['reason']);
    }

    public function testClassifyAutoNoSeConsideraColorValidoParaSet(): void
    {
        // 'auto' cae en reset, no en set. Esto evita que un caller
        // (modelo o controller) persista una fila con color='auto',
        // que seria funcionalmente equivalente a no tener override
        // pero ocupa espacio y confunde la invariante de la tabla.
        $d = UserColor::classifyOverrideSubmit(null, 'auto');
        $this->assertNotSame(UserColor::SUBMIT_SET, $d['action']);
    }
}
