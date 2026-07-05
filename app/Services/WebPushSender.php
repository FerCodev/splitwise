<?php

namespace App\Services;

use App\Models\PushSubscription;
use Config\Push;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushSender
{
    private Push $config;
    private EndpointValidator $validator;

    const ERROR_EXPIRED = 'expired';
    const ERROR_TRANSIENT_429 = 'transient_http_429';
    const ERROR_TRANSIENT_5XX = 'transient_http_5xx';
    const ERROR_PERMANENT_4XX = 'permanent_http_4xx';
    const ERROR_INVALID_ENDPOINT = 'invalid_endpoint';
    const ERROR_TRANSPORT = 'transport_error';

    const STATUS_SUCCESS = 'success';

    public function __construct(?EndpointValidator $validator = null)
    {
        $this->config = config(Push::class);
        $this->validator = $validator ?? new EndpointValidator();
    }

    public function isConfigured(): bool
    {
        return $this->config->isConfigured();
    }

    public function getPublicKey(): string
    {
        return $this->config->publicKey;
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

        if ($statusCode === 429) {
            return self::ERROR_TRANSIENT_429;
        }

        if ($statusCode >= 500 && $statusCode < 600) {
            return self::ERROR_TRANSIENT_5XX;
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return self::ERROR_PERMANENT_4XX;
        }

        return self::ERROR_TRANSPORT;
    }

    public function sendToAll(array $subscriptions, array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['success' => 0, 'expired' => 0, 'failed' => 0, 'details' => []];
        }

        if (empty($subscriptions)) {
            return ['success' => 0, 'expired' => 0, 'failed' => 0, 'details' => []];
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
        $allDetails = [];

        foreach ($subscriptions as $sub) {
            if (!$this->validator->isValid($sub['endpoint'])) {
                $subsModel->disable($sub['id'], $sub['user_id']);
                $allDetails[] = [
                    'push_subscription_id' => (int) $sub['id'],
                    'status' => self::ERROR_INVALID_ENDPOINT,
                ];
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
                $allDetails[] = [
                    'push_subscription_id' => (int) $sub['id'],
                    'status' => self::ERROR_INVALID_ENDPOINT,
                ];
            }
        }

        $result = ['success' => 0, 'expired' => 0, 'failed' => 0, 'details' => $allDetails];

        $i = 0;
        foreach ($webPush->flush() as $report) {
            $sub = $subscriptionMap[$i] ?? null;
            $i++;

            if ($report->isSuccess()) {
                $result['success']++;
                if ($sub) {
                    $subsModel->recordSuccess($sub['id']);
                }
                $result['details'][] = [
                    'push_subscription_id' => $sub ? (int) $sub['id'] : null,
                    'status' => self::STATUS_SUCCESS,
                ];
                continue;
            }

            $errorClass = $this->classifyError($report);

            switch ($errorClass) {
                case self::ERROR_EXPIRED:
                    $result['expired']++;
                    if ($sub) {
                        $subsModel->disable($sub['id'], $sub['user_id']);
                    }
                    break;
                case self::ERROR_TRANSIENT_429:
                case self::ERROR_TRANSIENT_5XX:
                case self::ERROR_TRANSPORT:
                    $result['failed']++;
                    if ($sub) {
                        $subsModel->recordFailure($sub['id']);
                    }
                    break;
                default:
                    $result['failed']++;
                    if ($sub) {
                        $subsModel->disable($sub['id'], $sub['user_id']);
                    }
                    break;
            }

            $result['details'][] = [
                'push_subscription_id' => $sub ? (int) $sub['id'] : null,
                'status' => $errorClass,
            ];
        }

        return $result;
    }
}
