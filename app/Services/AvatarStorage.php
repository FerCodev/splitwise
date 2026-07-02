<?php

namespace App\Services;

use Config\Avatar;
use RuntimeException;

class AvatarStorage
{
    public function __construct(private ?Avatar $config = null)
    {
        $this->config ??= config('Avatar');
    }

    public function directory(): string
    {
        return $this->config->directory;
    }

    public function ensureDirectory(): void
    {
        $directory = $this->directory();
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio de avatares.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('El directorio de avatares no tiene permisos de escritura.');
        }
    }

    public function randomFilename(string $extension): string
    {
        $extension = strtolower($extension);
        if (!in_array($extension, ['webp', 'jpg'], true)) {
            throw new RuntimeException('Formato de salida no permitido.');
        }
        do {
            $filename = bin2hex(random_bytes(24)) . '.' . $extension;
        } while (is_file($this->directory() . DIRECTORY_SEPARATOR . $filename));

        return $filename;
    }

    public function pathFor(string $filename): ?string
    {
        if ($filename === '' || basename($filename) !== $filename
            || preg_match('/\A[a-f0-9]{48}\.(?:webp|jpg)\z/', $filename) !== 1) {
            return null;
        }

        return $this->directory() . DIRECTORY_SEPARATOR . $filename;
    }

    public function delete(?string $filename): bool
    {
        if (!$filename || !($path = $this->pathFor($filename)) || !is_file($path)) {
            return true;
        }

        return @unlink($path);
    }
}
