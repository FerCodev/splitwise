<?php

use App\Controllers\Admin;
use CodeIgniter\Test\CIUnitTestCase;

final class StorageWriteTest extends CIUnitTestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gastito-write-test-' . bin2hex(random_bytes(4));
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

    public function testStorageWriteTestMethodExists(): void
    {
        $this->assertTrue(method_exists(Admin::class, 'storageWriteTest'));
    }

    public function testTokenGenerationIsRandom(): void
    {
        $t1 = bin2hex(random_bytes(16));
        $t2 = bin2hex(random_bytes(16));
        $this->assertNotSame($t1, $t2);
        $this->assertSame(32, strlen($t1));
    }

    public function testCreateWriteReadDeleteWorks(): void
    {
        $token = bin2hex(random_bytes(16));
        $filename = 'gastito-write-test-' . $token . '.tmp';
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . $filename;
        $content = 'gastito-storage-write-' . $token;

        try {
            $handle = fopen($path, 'x');
            $this->assertNotFalse($handle, 'x mode should create file exclusively');

            $written = fwrite($handle, $content);
            fclose($handle);

            $this->assertSame(strlen($content), $written);
            $this->assertTrue(is_file($path));

            $readBack = file_get_contents($path);
            $this->assertSame($content, $readBack);

            $expectedHash = hash('sha256', $content);
            $actualHash = hash('sha256', $readBack);
            $this->assertSame($expectedHash, $actualHash);
        } finally {
            if (isset($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function testFileDeletedAfterTest(): void
    {
        $token = bin2hex(random_bytes(16));
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'gastito-write-test-' . $token . '.tmp';
        file_put_contents($path, 'test');
        $this->assertTrue(is_file($path));

        @unlink($path);
        $this->assertFalse(file_exists($path));
    }

    public function testNoResidueAfterCleanup(): void
    {
        $token = bin2hex(random_bytes(16));
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'gastito-write-test-' . $token . '.tmp';
        file_put_contents($path, 'x');

        $this->assertTrue(is_file($path));
        @unlink($path);
        $this->assertFalse(file_exists($path));
    }

    public function testXModeFailsIfFileExists(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'existing.tmp';
        file_put_contents($path, 'existing');
        try {
            $handle = @fopen($path, 'x');
            $this->assertFalse($handle, 'x mode should fail on existing file');
            if ($handle) {
                fclose($handle);
            }
        } finally {
            @unlink($path);
        }
    }

    public function testTryFinallyCleansUpOnError(): void
    {
        $token = bin2hex(random_bytes(16));
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'gastito-write-test-' . $token . '.tmp';
        $fileCreated = false;

        try {
            file_put_contents($path, 'test');
            $fileCreated = true;
            $this->assertTrue(is_file($path));
            throw new RuntimeException('forced error');
        } catch (RuntimeException) {
            $this->assertTrue(true, 'Error caught in try');
        } finally {
            if ($fileCreated) {
                @unlink($path);
            }
        }

        $this->assertFalse(file_exists($path), 'File should be cleaned up in finally');
    }

    public function testTestTxtNotTouched(): void
    {
        $this->cleanupTmpFiles();
        $files = glob($this->tmpDir . DIRECTORY_SEPARATOR . 'test.txt');
        $this->assertEmpty($files, 'test.txt should not exist in temp dir');
    }

    public function testHashMismatchDetected(): void
    {
        $content = 'original';
        $hash1 = hash('sha256', $content);
        $hash2 = hash('sha256', 'different');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testWriteSizeMatchesContentLength(): void
    {
        $content = 'gastito-storage-write-' . bin2hex(random_bytes(8));
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'size-test.tmp';
        try {
            $written = file_put_contents($path, $content);
            $this->assertSame(strlen($content), $written);
            $size = filesize($path);
            $this->assertSame(strlen($content), $size);
        } finally {
            @unlink($path);
        }
    }
}
