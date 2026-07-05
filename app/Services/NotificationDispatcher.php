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
                'processed' => 0, 'sent' => 0, 'expired' => 0,
                'retried' => 0, 'failed' => 0,
                'error' => 'Web Push no está configurado.',
            ];
        }

        $outboxModel = new NotificationOutbox();
        $outboxModel->recoverOrphanJobs();

        $jobs = $outboxModel->getPendingJobs($limit);

        $result = [
            'processed' => 0, 'sent' => 0, 'expired' => 0,
            'retried' => 0, 'failed' => 0,
        ];

        foreach ($jobs as $job) {
            if (!$outboxModel->claimForProcessing($job['id'])) {
                continue;
            }

            $result['processed']++;

            $notification = (new Notification())->find($job['notification_id']);
            if (!$notification) {
                $outboxModel->markFailed($job['id'], 'notification_not_found');
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
                    'icon' => base_url('assets/pwa/icon-192.png'),
                    'badge' => base_url('assets/pwa/icon-192.png'),
                    'tag' => 'notif-' . $notification['id'],
                ];

                $sendResult = $this->sender->sendToAll($subscriptions, $payload);

                $result['sent'] += $sendResult['success'];
                $result['expired'] += $sendResult['expired'];

                $hasFailed = false;
                $errorClasses = [];
                foreach ($sendResult['details'] as $detail) {
                    $s = $detail['status'] ?? '';
                    if ($s !== 'success' && $s !== 'expired') {
                        $hasFailed = true;
                        $errorClasses[] = $s;
                    }
                }

                if ($hasFailed && $job['attempts'] >= NotificationOutbox::MAX_ATTEMPTS - 1) {
                    $outboxModel->markFailed($job['id'], implode(',', array_unique($errorClasses)));
                    $result['failed']++;
                } elseif ($hasFailed) {
                    $outboxModel->markRetry($job['id'], implode(',', array_unique($errorClasses)));
                    $result['retried']++;
                } else {
                    $outboxModel->markCompleted($job['id']);
                }
            } catch (\Exception $e) {
                $errorCode = WebPushSender::ERROR_TRANSPORT;
                $outboxModel->markRetry($job['id'], $errorCode);
                $result['retried']++;
            }
        }

        return $result;
    }
}
