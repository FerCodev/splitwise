<?php

namespace App\Services;

use App\Models\PushSubscription;
use Config\Push;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushSender
{
    private Push $config;

    const ERROR_EXPIRED = 'expired';
    const ERROR_TRANSIENT_429 = 'transient_http_429';
    const ERROR_TRANSIENT_5XX = 'transient_http_5xx';
    const ERROR_PERMANENT_4XX = 'permanent_http_4xx';
    const ERROR_INVALID_ENDPOINT = 'invalid_endpoint';
    const ERROR_TRANSPORT = 'transport_error';

    public function __construct()
    {
        $this->config = config(Push::class);
    }

    public function isConfigured(): bool
    {
        return $this->config->isConfigured();
    }

    public function getPublicKey(): string
    {
        return $this->config->publicKey;
    }

    public function isEndpointValid(string $endpoint): bool
    {
        $url = filter_var($endpoint, FILTER_VALIDATE_URL);
        if (!$url) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== 'https') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || $host === '') {
            return false;
        }

        $hostLower = strtolower($host);

        if (in_array($hostLower, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    public function classifyError(\Minishlink\WebPush\MessageSentReport $report): string
    {
        if ($report->isSubscriptionExpired()) {
            return self::ERROR_EXPIRED;
        }

        $response = $report->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;

        if ($statusCode === 404 || $statusCode === 410) {
            return self::ERROR_EXPIRED;
        }

        if ($statusCode === 429 || ($statusCode >= 500 && $statusCode < 600)) {
            return ($statusCode === 429) ? self::ERROR_TRANSIENT_429 : self::ERROR_TRANSIENT_5XX;
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return self::ERROR_PERMANENT_4XX;
        }

        return self::ERROR_TRANSPORT;
    }

    public function sendToAll(array $subscriptions, array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => 0, 'expired' => 0, 'failed' => 0, 'errors' => []];
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
            if (!$this->isEndpointValid($sub['endpoint'])) {
                $subsModel->disable($sub['id'], $sub['user_id']);
                continue;
            }

            try {
                $subscriptionObj = Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'publicKey' => $sub['public_key'],
                    'authToken' => $sub['auth_token'],
                    'contentEncoding' => $sub['content_encoding'] ?? 'aes128gcm',
                ]);
                $webPush->queueNotification($subscriptionObj, $payloadJson);
                $subscriptionMap[] = $sub;
            } catch (\Exception $e) {
                $subsModel->disable($sub['id'], $sub['user_id']);
            }
        }

        $result = [
            'success' => 0,
            'expired' => 0,
            'failed' => 0,
            'errors' => [],
            'details' => [],
        ];

        $i = 0;
        foreach ($webPush->flush() as $report) {
            $sub = $subscriptionMap[$i] ?? null;
            $i++;

            if ($report->isSuccess()) {
                $result['success']++;
                if ($sub) {
                    $subsModel->recordSuccess($sub['id']);
                    $result['details'][] = ['id' => $sub['id'], 'status' => 'success'];
                }
                continue;
            }

            $errorClass = $this->classifyError($report);

            if ($errorClass === self::ERROR_EXPIRED) {
                $result['expired']++;
                if ($sub) {
                    $subsModel->disable($sub['id'], $sub['user_id']);
                }
                $result['details'][] = ['id' => $sub['id'] ?? null, 'status' => 'expired'];
            } elseif (in_array($errorClass, [self::ERROR_TRANSIENT_429, self::ERROR_TRANSIENT_5XX, self::ERROR_TRANSPORT], true)) {
                $result['failed']++;
                if ($sub) {
                    $subsModel->recordFailure($sub['id']);
                }
                $result['details'][] = ['id' => $sub['id'] ?? null, 'status' => $errorClass];
            } else {
                $result['failed']++;
                if ($sub) {
                    $subsModel->disable($sub['id'], $sub['user_id']);
                }
                $result['details'][] = ['id' => $sub['id'] ?? null, 'status' => $errorClass];
            }
        }

        return $result;
    }
}
