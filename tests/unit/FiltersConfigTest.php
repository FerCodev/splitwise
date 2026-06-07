<?php

use Config\Filters;

/**
 * @internal
 */
final class FiltersConfigTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testCsrfGlobalFilterIsEnabled(): void
    {
        $config = new Filters();

        $this->assertContains('csrf', $config->globals['before']);
    }
}
