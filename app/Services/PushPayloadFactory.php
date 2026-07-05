<?php

namespace App\Services;

use Config\Push;

class PushPayloadFactory
{
    private Push $config;

    public function __construct()
    {
        $this->config = config(Push::class);
    }

    public function createForExpense(
        string $title,
        string $body,
        string $url,
        int $expenseId
    ): array {
        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => '/assets/pwa/icon-192.png',
            'badge' => '/assets/pwa/icon-192.png',
            'tag' => 'gasto-' . $expenseId,
        ];

        return $payload;
    }
}
