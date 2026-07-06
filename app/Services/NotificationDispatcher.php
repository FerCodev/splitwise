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

            $userId = (int) $notification['user_id'];

            try {
                $prefs = (new NotificationPreference())->getForUser($userId);

                if (empty($prefs['push_enabled'])) {
                    $outboxModel->markCompleted($job['id']);
                    continue;
                }

                $this->processNotificationDeliveries($notification, $userId, $outboxModel, $job, $result);
            } catch (\Exception $e) {
                $outboxModel->markRetry($job['id'], WebPushSender::ERROR_TRANSPORT);
                $result['retried']++;
            }
        }

        return $result;
    }

    private function processNotificationDeliveries(
        array $notification, int $userId,
        NotificationOutbox $outboxModel, array $job, array &$result
    ): void {
        $notificationId = (int) $notification['id'];

        $freshJob = $outboxModel->find($job['id']);
        if (!$freshJob) {
            return;
        }

        if (!$outboxModel->isDeliveriesInitialized($freshJob)) {
            $initialized = $this->initializeDeliverySnapshot($notificationId, $userId, $outboxModel, $freshJob);
            if (!$initialized) {
                return;
            }
        }

        $subsModel = new PushSubscription();
        $readModel = new NotificationDelivery();
        $pendingDeliveries = $readModel->getReadyForNotification($notificationId);

        if (empty($pendingDeliveries)) {
            if ($readModel->hasUnfinishedForNotification($notificationId)) {
                $nextAt = $readModel->nextAvailableAtForNotification($notificationId);
                $outboxModel->scheduleRetry($job['id'], 'pending_future', $nextAt ?? date('Y-m-d H:i:s'));
            } else {
                $outboxModel->markCompleted($job['id']);
            }
            return;
        }

        $pendingSubIds = array_column($pendingDeliveries, 'push_subscription_id');
        $pendingSubs = $subsModel->findEnabledByUserAndIds($userId, $pendingSubIds);

        $subById = [];
        foreach ($pendingSubs as $s) {
            $subById[(int) $s['id']] = $s;
        }

        $writeModel = new NotificationDelivery();
        foreach ($pendingDeliveries as $delivery) {
            $did = (int) $delivery['id'];
            $sid = (int) $delivery['push_subscription_id'];
            if (!isset($subById[$sid])) {
                $writeModel->markFailed($did, 'subscription_gone');
                $result['failed']++;
            }
        }
        unset($writeModel);

        $toSend = [];
        $deliveryById = [];
        foreach ($pendingDeliveries as $pd) {
            $sid = (int) $pd['push_subscription_id'];
            if (isset($subById[$sid])) {
                $toSend[] = $subById[$sid];
                $deliveryById[$sid] = $pd;
            }
        }

        if (empty($toSend)) {
            $finalRead = new NotificationDelivery();
            if ($finalRead->hasUnfinishedForNotification($notificationId)) {
                $nextAt = $finalRead->nextAvailableAtForNotification($notificationId);
                $outboxModel->scheduleRetry($job['id'], 'pending_future', $nextAt ?? date('Y-m-d H:i:s'));
            } else {
                $outboxModel->markCompleted($job['id']);
            }
            return;
        }

        $payload = [
            'title' => (string) ($notification['title'] ?? ''),
            'body' => (string) ($notification['body'] ?? ''),
            'url' => (string) ($notification['target_url'] ?? ''),
            'icon' => base_url('assets/pwa/icon-192.png'),
            'badge' => base_url('assets/pwa/notification-badge.png'),
            'tag' => 'notif-' . $notificationId,
        ];

        $sendResult = $this->sender->sendToAll($toSend, $payload);

        $sentSubIds = [];
        foreach ($toSend as $s) {
            $sentSubIds[(int) $s['id']] = true;
        }

        $detailSubIds = [];

        $writeModel2 = new NotificationDelivery();
        foreach ($sendResult['details'] as $detail) {
            $subId = $detail['push_subscription_id'] ?? null;
            $status = $detail['status'];
            if (!$subId || !isset($deliveryById[(int) $subId]) || !isset($sentSubIds[(int) $subId])) continue;

            $detailSubIds[(int) $subId] = true;
            $delivery = $deliveryById[(int) $subId];
            $did = (int) $delivery['id'];

            switch ($status) {
                case WebPushSender::STATUS_SUCCESS:
                    $writeModel2->markSuccess($did);
                    $result['sent']++;
                    break;
                case WebPushSender::ERROR_EXPIRED:
                    $writeModel2->markExpired($did);
                    $result['expired']++;
                    break;
                case WebPushSender::ERROR_INVALID_ENDPOINT:
                case WebPushSender::ERROR_PERMANENT_4XX:
                    $writeModel2->markFailed($did, $status);
                    $result['failed']++;
                    break;
                case WebPushSender::ERROR_TRANSIENT_429:
                case WebPushSender::ERROR_TRANSIENT_5XX:
                case WebPushSender::ERROR_TRANSPORT:
                    $writeModel2->markRetry($did, $status);
                    $result['retried']++;
                    break;
                default:
                    $writeModel2->markFailed($did, $status);
                    $result['failed']++;
                    break;
            }
        }

        foreach ($sentSubIds as $sid => $_) {
            if (!isset($detailSubIds[$sid]) && isset($deliveryById[$sid])) {
                $did = (int) $deliveryById[$sid]['id'];
                $writeModel2->markRetry($did, WebPushSender::ERROR_TRANSPORT);
                $result['retried']++;
            }
        }
        unset($writeModel2);

        $finalRead = new NotificationDelivery();
        if ($finalRead->hasUnfinishedForNotification($notificationId)) {
            $nextAt = $finalRead->nextAvailableAtForNotification($notificationId);
            $outboxModel->scheduleRetry($job['id'], 'partial_delivery', $nextAt ?? date('Y-m-d H:i:s', time() + 60));
        } else {
            $outboxModel->markCompleted($job['id']);
        }
    }

    private function initializeDeliverySnapshot(
        int $notificationId, int $userId,
        NotificationOutbox $outboxModel, array $job
    ): bool {
        $db = $outboxModel->db;
        $db->transBegin();

        try {
            $lockedJob = $outboxModel->lockAndGetJob($job['id']);
            if (!$lockedJob) {
                $db->transRollback();
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            if ($outboxModel->isDeliveriesInitialized($lockedJob)) {
                $db->transCommit();
                return true;
            }

            $subsModel = new PushSubscription();
            $allSubs = $subsModel->findEnabledByUser($userId);
            $allSubIds = array_column($allSubs, 'id');

            $deliveryModel = new NotificationDelivery();

            if (!empty($allSubIds)) {
                $existingCount = $deliveryModel->where('notification_id', $notificationId)->countAllResults();
                if ($existingCount > 0) {
                    $deliveryModel->cleanupPartialSnapshot($notificationId);
                }

                $inserted = $deliveryModel->insertSnapshot($notificationId, $allSubIds);

                if ($inserted !== count($allSubIds)) {
                    $db->transRollback();
                    $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                    return false;
                }
            }

            $markerResult = $outboxModel->markDeliveriesInitialized($job['id']);
            if (!$markerResult) {
                $db->transRollback();
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            if (!$outboxModel->verifyMarkerSet($job['id'])) {
                $db->transRollback();
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            $finalDeliveryCount = $deliveryModel->where('notification_id', $notificationId)->countAllResults();
            if ($finalDeliveryCount !== count($allSubIds)) {
                $db->transRollback();
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            if (!$db->transStatus()) {
                $db->transRollback();
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            $commitResult = $db->transCommit();
            if (!$commitResult) {
                $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
                return false;
            }

            if (empty($allSubIds)) {
                $outboxModel->markCompleted($job['id']);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            $outboxModel->scheduleRetry($job['id'], 'delivery_initialization_failed', date('Y-m-d H:i:s', time() + 60));
            return false;
        }
    }
}
