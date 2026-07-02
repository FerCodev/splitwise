<?php

namespace App\Services;

use Config\Avatar;
use RuntimeException;

class AvatarImageProcessor
{
    private const MIME_LOADERS = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];

    public function __construct(private ?AvatarStorage $storage = null, private ?Avatar $config = null)
    {
        $this->config ??= config('Avatar');
        $this->storage ??= new AvatarStorage($this->config);
    }

    public function process(string $source, int $size): string
    {
        if ($size <= 0 || $size > $this->config->maxBytes) {
            throw new RuntimeException('La imagen no puede superar 5 MB.');
        }
        if (!extension_loaded('gd')) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        $info = @getimagesize($source);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';
        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        if (!isset(self::MIME_LOADERS[$mime])) {
            throw new RuntimeException('El archivo debe ser una imagen JPG, PNG o WebP.');
        }
        if ($width < 1 || $height < 1 || $width > $this->config->maxDimension
            || $height > $this->config->maxDimension || ($width * $height) > $this->config->maxPixels) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        $loader = self::MIME_LOADERS[$mime];
        if (!function_exists($loader) || !($image = @$loader($source))) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        try {
            if ($mime === 'image/jpeg') {
                $image = $this->orientJpeg($image, $source);
                $width = imagesx($image);
                $height = imagesy($image);
            }
            $output = $this->cropSquare($image, $width, $height);
            $extension = function_exists('imagewebp') ? 'webp' : 'jpg';
            $this->storage->ensureDirectory();
            $filename = $this->storage->randomFilename($extension);
            $path = $this->storage->pathFor($filename);
            $saved = $extension === 'webp'
                ? @imagewebp($output, $path, $this->config->webpQuality)
                : @imagejpeg($output, $path, $this->config->jpegQuality);
            imagedestroy($output);
            if (!$saved || !$path || !is_file($path) || @getimagesize($path) === false) {
                if ($path && is_file($path)) {
                    @unlink($path);
                }
                throw new RuntimeException('No se pudo guardar la foto. Intent&aacute; nuevamente.');
            }

            return $filename;
        } finally {
            imagedestroy($image);
        }
    }

    private function cropSquare($image, int $width, int $height)
    {
        $side = min($width, $height);
        $sourceX = (int) floor(($width - $side) / 2);
        $sourceY = (int) floor(($height - $side) / 2);
        $size = $this->config->outputSize;
        $output = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($output, 255, 255, 255);
        imagefill($output, 0, 0, $white);
        imagecopyresampled($output, $image, 0, 0, $sourceX, $sourceY, $size, $size, $side, $side);

        return $output;
    }

    private function orientJpeg($image, string $source)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($source);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $angle = match ($orientation) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
        if ($angle === 0 || !($rotated = @imagerotate($image, $angle, 0))) {
            return $image;
        }
        imagedestroy($image);

        return $rotated;
    }
}
