<?php

namespace App\Services;

use App\Models\PushSubscription;
use Config\Push;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushSender
{
    private Push $config;

    public function __construct()
    {
        $this->config = config(Push::class);
    }

    public function isConfigured(): bool
    {
        return $this->config->isConfigured();
    }

    public function sendToAll(array $subscriptions, array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => 0,
                'expired' => 0,
                'failed' => 0,
                'errors' => ['Web Push no está configurado.'],
            ];
        }

        if (empty($subscriptions)) {
            return ['success' => 0, 'expired' => 0, 'failed' => 0, 'errors' => []];
        }

        $auth = [
            'VAPID' => [
                'subject' => $this->config->subject,
                'publicKey' => $this->config->publicKey,
                'privateKey' => $this->config->privateKey,
            ],
        ];

        $webPush = new WebPush($auth);
        $webPush->setAutomaticPadding(0);

        $subsModel = new PushSubscription();
        $payloadJson = json_encode($payload);

        $subscriptionMap = [];
        foreach ($subscriptions as $sub) {
            $subscriptionObj = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'publicKey' => $sub['public_key'],
                'authToken' => $sub['auth_token'],
                'contentEncoding' => $sub['content_encoding'] ?? 'aes128gcm',
            ]);

            $webPush->queueNotification($subscriptionObj, $payloadJson);
            $subscriptionMap[] = $sub;
        }

        $result = ['success' => 0, 'expired' => 0, 'failed' => 0, 'errors' => []];
        $i = 0;

        foreach ($webPush->flush() as $report) {
            $sub = $subscriptionMap[$i] ?? null;
            $i++;

            if ($report->isSuccess()) {
                $result['success']++;
                if ($sub) {
                    $subsModel->recordSuccess($sub['id']);
                }
            } elseif ($report->isSubscriptionExpired()) {
                $result['expired']++;
                if ($sub) {
                    $subsModel->disable($sub['id'], $sub['user_id']);
                }
            } else {
                $result['failed']++;
                $reason = $report->getReason() ?: 'Unknown error';
                $result['errors'][] = 'Failed: ' . substr($reason, 0, 200);
                if ($sub) {
                    $subsModel->recordFailure($sub['id']);
                }
            }
        }

        return $result;
    }

    public function getPublicKey(): string
    {
        return $this->config->publicKey;
    }
}
