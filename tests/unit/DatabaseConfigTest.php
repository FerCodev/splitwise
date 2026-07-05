<?php

use Config\Database;

/**
 * @internal
 */
final class DatabaseConfigTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testTestsGroupUsesTestSuffixedDatabase(): void
    {
        $this->assertStringEndsWith('_test', env('database.tests.database') ?: '',
            'El grupo tests debe apuntar a una base con sufijo _test');
    }

    public function testGuardRejectsNonTestDatabase(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('_test');

        Database::assertTestDatabaseName('splitwise');
    }

    public function testGuardAcceptsTestSuffixedDatabase(): void
    {
        // No debe lanzar excepcion
        Database::assertTestDatabaseName('splitwise_test');
        $this->assertTrue(true);
    }
}
