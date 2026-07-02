<?php

use App\Services\AvatarImageProcessor;
use App\Services\AvatarPersistence;
use App\Services\AvatarStorage;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Avatar;

final class AvatarTest extends CIUnitTestCase
{
    private string $directory;
    private Avatar $config;
    private AvatarStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gastito-avatar-' . bin2hex(random_bytes(8));
        $this->config = new Avatar();
        $this->config->directory = $this->directory;
        $this->storage = new AvatarStorage($this->config);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            foreach (glob($this->directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->directory);
        }
        parent::tearDown();
    }

    public function testDefaultDirectoryIsOutsideRepository(): void
    {
        $config = new Avatar();
        $expected = dirname(rtrim(ROOTPATH, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'gastito' . DIRECTORY_SEPARATOR . 'avatars';
        $this->assertSame($expected, $config->directory);
        $this->assertFalse(str_starts_with($config->directory, rtrim(ROOTPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR));
    }

    public function testEnvironmentOverride(): void
    {
        $key = 'avatar.storageDirectory';
        $_ENV[$key] = $this->directory;
        $_SERVER[$key] = $this->directory;
        $config = new Avatar();
        unset($_ENV[$key], $_SERVER[$key]);
        $this->assertSame($this->directory, $config->directory);
    }

    public function testRandomFilenameAndSafeBasename(): void
    {
        $first = $this->storage->randomFilename('webp');
        $second = $this->storage->randomFilename('webp');
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\.webp\z/', $first);
        $this->assertNotSame($first, $second);
        $this->assertSame($first, basename($first));
        $this->assertNull($this->storage->pathFor('../' . $first));
        $this->assertNull($this->storage->pathFor('evil.php'));
    }

    /**
     * @dataProvider validImageProvider
     */
    public function testValidImagesAreNormalized(string $format, int $width, int $height): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD no esta habilitado en este proceso.');
        }
        if ($format === 'webp' && !function_exists('imagewebp')) {
            $this->markTestSkipped('GD no soporta WebP.');
        }
        $source = $this->makeImage($format, $width, $height);
        file_put_contents($source, file_get_contents($source) . 'PRIVATE-METADATA');
        $filename = (new AvatarImageProcessor($this->storage, $this->config))->process($source, filesize($source));
        $output = $this->storage->pathFor($filename);
        $info = getimagesize($output);

        $this->assertSame(256, $info[0]);
        $this->assertSame(256, $info[1]);
        $this->assertContains($info['mime'], ['image/webp', 'image/jpeg']);
        $this->assertNotFalse(@imagecreatefromstring(file_get_contents($output)));
        $this->assertStringNotContainsString('PRIVATE-METADATA', file_get_contents($output));
        $this->assertSame(1, count(glob($this->directory . DIRECTORY_SEPARATOR . '*') ?: []));
        @unlink($source);
    }

    public static function validImageProvider(): array
    {
        return [['jpeg', 480, 240], ['png', 240, 480], ['webp', 300, 300]];
    }

    public function testNonImageSvgFalseExtensionAndCorruptAreRejected(): void
    {
        $processor = new AvatarImageProcessor($this->storage, $this->config);
        foreach ([
            'text.jpg' => 'not an image',
            'vector.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
            'fake.png' => '<?php echo 1;',
            'corrupt.jpg' => "\xFF\xD8broken",
        ] as $name => $contents) {
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(4)) . '-' . $name;
            file_put_contents($path, $contents);
            try {
                $processor->process($path, filesize($path));
                $this->fail($name . ' debio rechazarse.');
            } catch (RuntimeException $e) {
                $this->assertNotSame('', $e->getMessage());
            } finally {
                @unlink($path);
            }
        }
    }

    public function testOversizedFileIsRejectedBeforeDecode(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'avatar-');
        file_put_contents($path, 'x');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('5 MB');
        try {
            (new AvatarImageProcessor($this->storage, $this->config))->process($path, $this->config->maxBytes + 1);
        } finally {
            @unlink($path);
        }
    }

    public function testReplacementDeletesOldOnlyAfterPersistence(): void
    {
        $this->storage->ensureDirectory();
        $old = $this->storage->randomFilename('jpg');
        $new = $this->storage->randomFilename('jpg');
        file_put_contents($this->storage->pathFor($old), 'old');
        file_put_contents($this->storage->pathFor($new), 'new');

        (new AvatarPersistence($this->storage))->replace($old, $new, function () use ($old): void {
            $this->assertFileExists($this->storage->pathFor($old));
        });

        $this->assertFileDoesNotExist($this->storage->pathFor($old));
        $this->assertFileExists($this->storage->pathFor($new));
    }

    public function testDatabaseFailureDeletesNewAndKeepsOld(): void
    {
        $this->storage->ensureDirectory();
        $old = $this->storage->randomFilename('jpg');
        $new = $this->storage->randomFilename('jpg');
        file_put_contents($this->storage->pathFor($old), 'old');
        file_put_contents($this->storage->pathFor($new), 'new');

        try {
            (new AvatarPersistence($this->storage))->replace($old, $new, static function (): void {
                throw new RuntimeException('db failed');
            });
            $this->fail('Debio propagar el error.');
        } catch (RuntimeException $e) {
            $this->assertSame('db failed', $e->getMessage());
        }
        $this->assertFileExists($this->storage->pathFor($old));
        $this->assertFileDoesNotExist($this->storage->pathFor($new));
    }

    public function testDeleteWithMissingFileStillPersists(): void
    {
        $persisted = false;
        (new AvatarPersistence($this->storage))->remove(str_repeat('a', 48) . '.webp', function () use (&$persisted): void {
            $persisted = true;
        });
        $this->assertTrue($persisted);
    }

    public function testAvatarComponentRendersImageFallbackAndEscapesName(): void
    {
        $filename = str_repeat('a', 48) . '.webp';
        $image = view('components/avatar', ['userId' => 7, 'name' => '<Admin>', 'avatarFilename' => $filename, 'avatarUpdatedAt' => '2026-07-01 12:00:00']);
        $fallback = view('components/avatar', ['userId' => 7, 'name' => '<Admin>', 'avatarFilename' => null, 'avatarUpdatedAt' => null]);
        $this->assertStringContainsString('<img ', $image);
        $this->assertStringContainsString('/usuarios/7/avatar', $image);
        $this->assertStringContainsString('?v=', $image);
        $this->assertStringNotContainsString('<Admin>', $image);
        $this->assertStringContainsString('user-avatar-fallback', $fallback);
        $this->assertStringContainsString('&lt;', $fallback);
        $this->assertStringNotContainsString($this->directory, $image . $fallback);
    }

    public function testRoutesAndWritesAreAuthenticatedAndSessionScoped(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $controller = file_get_contents(APPPATH . 'Controllers/Perfil.php');
        $navbar = file_get_contents(APPPATH . 'Views/partials/_navbar.php');
        $profile = file_get_contents(APPPATH . 'Views/perfil/index.php');
        $balance = file_get_contents(APPPATH . 'Views/grupos/balance.php');

        $this->assertStringContainsString("post('/perfil/avatar', 'Perfil::avatarUpload', ['filter' => 'auth'])", $routes);
        $this->assertStringContainsString("post('/perfil/avatar/eliminar', 'Perfil::avatarDelete', ['filter' => 'auth'])", $routes);
        $this->assertStringContainsString("get('/usuarios/(:num)/avatar', 'Perfil::avatar/$1', ['filter' => 'auth'])", $routes);
        $this->assertStringContainsString("session()->get('userId')", $controller);
        $this->assertStringNotContainsString("getPost('user_id')", $controller);
        $this->assertStringContainsString("view('components/avatar'", $navbar);
        $this->assertStringContainsString("view('components/avatar'", $profile);
        $this->assertStringContainsString("view('components/avatar'", $balance);
        $this->assertStringNotContainsString('/public_html/', $controller);
    }

    private function makeImage(string $format, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 80, 180));
        imagefilledrectangle($image, (int) ($width / 2), 0, $width, $height, imagecolorallocate($image, 230, 120, 20));
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(8)) . '.' . $format;
        match ($format) {
            'jpeg' => imagejpeg($image, $path, 90),
            'png' => imagepng($image, $path),
            'webp' => imagewebp($image, $path, 90),
        };
        imagedestroy($image);

        return $path;
    }
}
