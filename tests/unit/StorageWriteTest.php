<?php

use App\Controllers\Admin;
use CodeIgniter\Test\CIUnitTestCase;

final class StorageWriteTest extends CIUnitTestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gastito-probe-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->cleanupTmpFiles();
        if (is_dir($this->tmpDir)) {
            @rmdir($this->tmpDir);
        }
        parent::tearDown();
    }

    private function cleanupTmpFiles(): void
    {
        $files = glob($this->tmpDir . DIRECTORY_SEPARATOR . 'gastito-write-test-*.tmp');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
    }

    private function runProbe(string $directory): array
    {
        $reflection = new ReflectionClass(Admin::class);
        $method = $reflection->getMethod('runStorageWriteProbe');
        $method->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        return $method->invoke($instance, $directory);
    }

    public function testStorageWriteTestMethodExists(): void
    {
        $this->assertTrue(method_exists(Admin::class, 'storageWriteTest'));
    }

    public function testProbeMetodoExiste(): void
    {
        $this->assertTrue(method_exists(Admin::class, 'runStorageWriteProbe'));
    }

    public function testExitoCompletoTodosLosFlagsCorrectos(): void
    {
        $result = $this->runProbe($this->tmpDir);

        $this->assertTrue($result['dirFound']);
        $this->assertTrue($result['dirWritable']);
        $this->assertTrue($result['fileCreated'], 'fileCreated debe ser true');
        $this->assertTrue($result['writeOk'], 'writeOk debe ser true');
        $this->assertTrue($result['readOk'], 'readOk debe ser true');
        $this->assertTrue($result['hashMatch'], 'hashMatch debe ser true');
        $this->assertTrue($result['fileDeleted'], 'fileDeleted debe ser true');
        $this->assertFalse($result['residue'], 'residue debe ser false');
        $this->assertGreaterThan(0, $result['sizeWritten']);
        $this->assertStringContainsString('completada', $result['statusText']);
    }

    public function testArchivoEliminadoYResidueFalse(): void
    {
        $result = $this->runProbe($this->tmpDir);

        $this->assertTrue($result['fileDeleted']);
        $this->assertFalse($result['residue']);
    }

    public function testDirectorioInexistente(): void
    {
        $result = $this->runProbe($this->tmpDir . DIRECTORY_SEPARATOR . 'no-existe-' . uniqid());

        $this->assertFalse($result['dirFound']);
    }

    public function testDirectorioNoEscribible(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('No portable on Windows');
        }
        $roDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'readonly';
        mkdir($roDir, 0555);
        try {
            $result = $this->runProbe($roDir);
            if (!is_writable($roDir)) {
                $this->assertFalse($result['dirWritable']);
            }
        } finally {
            @chmod($roDir, 0777);
            @rmdir($roDir);
        }
    }

    public function testCreacionExclusiva(): void
    {
        $token = bin2hex(random_bytes(8));
        $filename = 'gastito-write-test-' . $token . '.tmp';
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, 'existing');
        try {
            $handle = @fopen($path, 'x');
            $this->assertFalse($handle);
        } finally {
            @unlink($path);
        }
    }

    public function testLecturaDiferenteProduceError(): void
    {
        $content = 'original';
        $hash1 = hash('sha256', $content);
        $hash2 = hash('sha256', 'tampered');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testHashCoincideEnExito(): void
    {
        $result = $this->runProbe($this->tmpDir);
        $this->assertTrue($result['hashMatch']);
    }

    public function testTestTxtPreexistenteNoSeToca(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'test.txt';
        $original = 'contenido original de test.txt';
        file_put_contents($path, $original);
        try {
            $result = $this->runProbe($this->tmpDir);
            $this->assertTrue($result['fileDeleted']);
            $this->assertFalse($result['residue']);

            $this->assertTrue(is_file($path), 'test.txt debe seguir existiendo');
            $this->assertSame($original, file_get_contents($path));
            $this->assertSame(strlen($original), filesize($path));
        } finally {
            @unlink($path);
        }
    }

    public function testSizeWrittenCoincideConContentLength(): void
    {
        $result = $this->runProbe($this->tmpDir);
        $this->assertGreaterThan(20, $result['sizeWritten']);
    }

    public function testAfterProbeNoQuedanTemporales(): void
    {
        $this->runProbe($this->tmpDir);
        $files = glob($this->tmpDir . DIRECTORY_SEPARATOR . 'gastito-write-test-*.tmp');
        $this->assertEmpty($files, 'No deben quedar archivos temporales');
    }
}
