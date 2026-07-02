<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Avatar extends BaseConfig
{
    public string $directory;
    public int $maxBytes = 5242880;
    public int $maxDimension = 8000;
    public int $maxPixels = 40000000;
    public int $outputSize = 256;
    public int $webpQuality = 82;
    public int $jpegQuality = 85;

    public function __construct()
    {
        parent::__construct();
        $default = dirname(rtrim(ROOTPATH, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'storage'
            . DIRECTORY_SEPARATOR . 'gastito' . DIRECTORY_SEPARATOR . 'avatars';
        $override = trim((string) env('avatar.storageDirectory', ''));
        $this->directory = $override !== '' ? rtrim($override, DIRECTORY_SEPARATOR) : $default;
    }
}
