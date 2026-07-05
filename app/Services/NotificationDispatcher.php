<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationOutbox;
use App\Models\NotificationDelivery;
use App\Models\PushSubscription;
use App\Models\NotificationPreference;

class NotificationDispatcher
{
    private WebPushSender $sender;

    public function __construct(?WebPushSender $sender = null)
    {
        $this->sender = $sender ?? new WebPushSender();
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
                $allSubs = $subsModel->findEnabledByUser($notification['user_id']);
                $allSubIds = array_column($allSubs, 'id');

                if (empty($allSubs)) {
                    $outboxModel->markCompleted($job['id']);
                    continue;
                }

                $deliveryModel = new NotificationDelivery();
                $deliveryModel->ensureForNotification($notification['id'], $allSubIds);

                $pendingDeliveries = $deliveryModel->getPendingForNotification($notification['id']);
                if (empty($pendingDeliveries)) {
                    $outboxModel->markCompleted($job['id']);
                    continue;
                }

                $pendingSubIds = array_column($pendingDeliveries, 'push_subscription_id');
                $pendingSubs = $subsModel->findByIds($pendingSubIds);

                $subById = [];
                foreach ($pendingSubs as $s) {
                    $subById[(int) $s['id']] = $s;
                }

                $payload = [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'url' => $notification['target_url'],
                    'icon' => base_url('assets/pwa/icon-192.png'),
                    'badge' => base_url('assets/pwa/icon-192.png'),
                    'tag' => 'notif-' . $notification['id'],
                ];

                $sendResult = $this->sender->sendToAll($pendingSubs, $payload);

                foreach ($sendResult['details'] as $detail) {
                    $subId = $detail['push_subscription_id'] ?? null;
                    $status = $detail['status'];
                    if (!$subId) continue;

                    $delivery = null;
                    foreach ($pendingDeliveries as $pd) {
                        if ((int) $pd['push_subscription_id'] === (int) $subId) {
                            $delivery = $pd;
                            break;
                        }
                    }
                    if (!$delivery) continue;

                    switch ($status) {
                        case WebPushSender::STATUS_SUCCESS:
                            $deliveryModel->markSuccess($delivery['id']);
                            $result['sent']++;
                            break;
                        case WebPushSender::ERROR_EXPIRED:
                            $deliveryModel->markExpired($delivery['id']);
                            $result['expired']++;
                            break;
                        case WebPushSender::ERROR_INVALID_ENDPOINT:
                        case WebPushSender::ERROR_PERMANENT_4XX:
                            $deliveryModel->markFailed($delivery['id'], $status);
                            $result['failed']++;
                            break;
                        case WebPushSender::ERROR_TRANSIENT_429:
                        case WebPushSender::ERROR_TRANSIENT_5XX:
                        case WebPushSender::ERROR_TRANSPORT:
                            $deliveryModel->markRetry($delivery['id'], $status);
                            $result['retried']++;
                            break;
                        default:
                            $deliveryModel->markFailed($delivery['id'], $status);
                            $result['failed']++;
                            break;
                    }
                }

                if ($deliveryModel->hasPendingForNotification($notification['id'])) {
                    $outboxModel->markRetry($job['id'], 'partial_delivery');
                } else {
                    $outboxModel->markCompleted($job['id']);
                }
            } catch (\Exception $e) {
                $outboxModel->markRetry($job['id'], WebPushSender::ERROR_TRANSPORT);
                $result['retried']++;
            }
        }

        return $result;
    }
}
