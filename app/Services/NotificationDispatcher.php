<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationOutbox;
use App\Models\PushSubscription;
use App\Models\NotificationPreference;

class NotificationDispatcher
{
    private WebPushSender $sender;

    public function __construct()
    {
        $this->sender = new WebPushSender();
    }

    public function dispatch(int $limit = 50): array
    {
        if (!$this->sender->isConfigured()) {
            return [
                'processed' => 0,
                'sent' => 0,
                'expired' => 0,
                'retried' => 0,
                'failed' => 0,
                'error' => 'Web Push no está configurado. Verificá push.vapidSubject, push.vapidPublicKey y push.vapidPrivateKey.',
            ];
        }

        $outboxModel = new NotificationOutbox();
        $jobs = $outboxModel->getPendingJobs($limit);

        $result = [
            'processed' => 0,
            'sent' => 0,
            'expired' => 0,
            'retried' => 0,
            'failed' => 0,
        ];

        foreach ($jobs as $job) {
            if (!$outboxModel->claimForProcessing($job['id'])) {
                continue;
            }

            $result['processed']++;

            $notification = (new Notification())->find($job['notification_id']);
            if (!$notification) {
                $outboxModel->markRetry($job['id'], 'Notification not found');
                $result['failed']++;
                continue;
            }

            try {
                $prefs = (new NotificationPreference())->getForUser($notification['user_id']);

                if (empty($prefs['push_enabled'])) {
                    $outboxModel->markCompleted($job['id']);
                    continue;
                }

                $subsModel = new PushSubscription();
                $subscriptions = $subsModel->findEnabledByUser($notification['user_id']);

                if (empty($subscriptions)) {
                    $outboxModel->markCompleted($job['id']);
                    continue;
                }

                $payload = [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'url' => $notification['target_url'],
                    'icon' => '/assets/pwa/icon-192.png',
                    'badge' => '/assets/pwa/icon-192.png',
                    'tag' => 'notif-' . $notification['id'],
                ];

                $sendResult = $this->sender->sendToAll($subscriptions, $payload);

                $result['sent'] += $sendResult['success'];
                $result['expired'] += $sendResult['expired'];

                if ($sendResult['failed'] > 0 || !empty($sendResult['errors'])) {
                    $errorMsg = implode('; ', $sendResult['errors']);
                    $outboxModel->markRetry($job['id'], $errorMsg);
                    $result['retried'] += ($sendResult['failed'] > 0) ? $sendResult['failed'] : 1;
                } else {
                    $outboxModel->markCompleted($job['id']);
                }
            } catch (\Exception $e) {
                $outboxModel->markRetry($job['id'], get_class($e) . ': ' . $e->getMessage());
                $result['failed']++;
            }
        }

        return $result;
    }
}
