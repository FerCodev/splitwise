<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Push extends BaseConfig
{
    public string $subject;
    public string $publicKey;
    public string $privateKey;

    public function __construct()
    {
        parent::__construct();
        $this->subject = trim((string) env('push.vapidSubject', ''));
        $this->publicKey = trim((string) env('push.vapidPublicKey', ''));
        $this->privateKey = trim((string) env('push.vapidPrivateKey', ''));
    }

    public function isConfigured(): bool
    {
        return $this->subject !== '' && $this->publicKey !== '' && $this->privateKey !== '';
    }
}
