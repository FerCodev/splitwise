<?php

use App\Filters\Utf8CharsetFilter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use Config\App;

/**
 * @internal
 */
final class EncodingRegressionTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testUtf8CharsetFilterAddsCharsetToHtmlResponses(): void
    {
        $response = new Response(new App());
        $response->setContentType('text/html');

        (new Utf8CharsetFilter())->after($this->createMock(RequestInterface::class), $response);

        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testUtf8CharsetFilterDoesNotOverrideJsonResponses(): void
    {
        $response = new Response(new App());
        $response->setContentType('application/json', 'UTF-8');

        (new Utf8CharsetFilter())->after($this->createMock(RequestInterface::class), $response);

        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testVersionedTextFilesDoNotContainAccidentalMojibake(): void
    {
        $allowed = array_map('realpath', [
            ROOTPATH . 'public/doc/index.html',
            ROOTPATH . 'Documentacion/roadmaps/historicos/roadmap-activo.md',
        ]);
        $patterns = [
            "\xC3\x83", // UTF-8 bytes for common mojibake prefix A-tilde.
            "\xC3\x82", // UTF-8 bytes for common mojibake prefix A-circumflex.
            "\xC3\xA2", // UTF-8 bytes for common mojibake prefix a-circumflex.
            "\xEF\xBF\xBD", // replacement character
        ];
        $extensions = ['php', 'css', 'js', 'md', 'html'];
        $directories = ['app', 'public', 'Documentacion', 'tests'];
        $failures = [];

        foreach ($directories as $directory) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(ROOTPATH . $directory, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), $extensions, true)) {
                    continue;
                }

                $path = $file->getRealPath();
                if (in_array($path, $allowed, true)) {
                    continue;
                }

                $contents = file_get_contents($path);
                foreach ($patterns as $pattern) {
                    if (str_contains($contents, $pattern)) {
                        $failures[] = str_replace(ROOTPATH, '', $path);
                        break;
                    }
                }
            }
        }

        $this->assertSame([], $failures);
    }
}
