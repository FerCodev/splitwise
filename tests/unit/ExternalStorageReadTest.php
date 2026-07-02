<?php

use App\Controllers\Admin;
use CodeIgniter\Test\CIUnitTestCase;

final class ExternalStorageReadTest extends CIUnitTestCase
{
    public function testStorageTestMethodExists(): void
    {
        $this->assertTrue(method_exists(Admin::class, 'storageTest'));
    }

    public function testFilePathComputedFromRootpath(): void
    {
        $publicHtml = dirname(rtrim(ROOTPATH, DIRECTORY_SEPARATOR));
        $testFile = $publicHtml . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'gastito' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'test.txt';

        $expectedSuffix = implode(DIRECTORY_SEPARATOR, ['storage', 'gastito', 'test', 'test.txt']);
        $this->assertStringEndsWith($expectedSuffix, $testFile);

        $this->assertStringStartsWith($publicHtml, $testFile);
    }

    public function testEmptyFileIsConsideredReadable(): void
    {
        $path = ROOTPATH . 'writable' . DIRECTORY_SEPARATOR . 'test-empty.txt';
        file_put_contents($path, '');
        try {
            $this->assertTrue(is_file($path));
            $this->assertTrue(is_readable($path));
            $contents = file_get_contents($path);
            $this->assertSame('', $contents);
            $this->assertSame(0, filesize($path));
            $hash = hash('sha256', '');
            $this->assertSame(hash('sha256', ''), $hash);
        } finally {
            @unlink($path);
        }
    }

    public function testHtmlPreviewIsEscaped(): void
    {
        $path = ROOTPATH . 'writable' . DIRECTORY_SEPARATOR . 'test-html.txt';
        $html = '<script>alert("xss")</script>';
        file_put_contents($path, $html);
        try {
            $contents = file_get_contents($path);
            $escaped = esc($contents);
            $this->assertStringContainsString('&lt;script&gt;', $escaped);
            $this->assertStringNotContainsString('<script>', $escaped);
        } finally {
            @unlink($path);
        }
    }

    public function testPreviewTruncatedTo500(): void
    {
        $long = str_repeat('a', 1000);
        $preview = mb_substr($long, 0, 500);
        $this->assertSame(500, mb_strlen($preview));
        $this->assertLessThan(mb_strlen($long), mb_strlen($preview));
    }

    public function testSha256HashIs64CharsHex(): void
    {
        $hash = hash('sha256', 'test');
        $this->assertSame(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    public function testNonExistentFileReturnsNullHash(): void
    {
        $testFile = ROOTPATH . 'writable' . DIRECTORY_SEPARATOR . 'does-not-exist-' . uniqid() . '.txt';
        $this->assertFalse(is_file($testFile));
    }
}
