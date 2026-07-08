<?php

use App\Services\Username;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class UsernameTest extends CIUnitTestCase
{
    public function testNormalizeRemovesAtAndLowercases(): void
    {
        $this->assertSame('fernando.dev', Username::normalize(' @Fernando.Dev '));
    }

    /** @dataProvider invalidUsernames */
    public function testRejectsInvalidFormats(string $value): void
    {
        $this->assertNotNull(Username::error($value));
    }

    public static function invalidUsernames(): array
    {
        return [['ab'], ['usuario-uno'], ['.usuario'], ['usuario.'], ['usu ario'], ['administraci&oacute;n']];
    }

    public function testRejectsReservedUsername(): void
    {
        $this->assertNotNull(Username::error('admin'));
    }
}
