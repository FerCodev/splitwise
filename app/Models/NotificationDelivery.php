<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationDelivery extends Model
{
    protected $table = 'notification_deliveries';
    protected $primaryKey = 'id';
    protected $allowedFields = ['notification_id', 'push_subscription_id', 'status', 'attempts', 'available_at', 'processed_at', 'last_error'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRY = 'retry';

    const MAX_ATTEMPTS = 5;

    public function ensureForNotification(int $notificationId, array $subscriptionIds): void
    {
        if (empty($subscriptionIds)) {
            return;
        }

        $hasAny = $this->where('notification_id', $notificationId)->countAllResults() > 0;

        if ($hasAny) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($subscriptionIds as $subId) {
            $this->insert([
                'notification_id' => $notificationId,
                'push_subscription_id' => $subId,
                'status' => self::STATUS_PENDING,
                'attempts' => 0,
                'available_at' => $now,
            ]);
        }
    }

    public function getReadyForNotification(int $notificationId, int $limit = 20): array
    {
        return $this->where('notification_id', $notificationId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_RETRY])
            ->where('available_at <=', date('Y-m-d H:i:s'))
            ->where('attempts <', self::MAX_ATTEMPTS)
            ->orderBy('available_at', 'ASC')
            ->findAll($limit);
    }

    public function hasUnfinishedForNotification(int $notificationId): bool
    {
        return $this->where('notification_id', $notificationId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_RETRY])
            ->countAllResults() > 0;
    }

    public function nextAvailableAtForNotification(int $notificationId): ?string
    {
        $row = $this->select('available_at')
            ->where('notification_id', $notificationId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_RETRY])
            ->orderBy('available_at', 'ASC')
            ->first();

        return $row ? $row['available_at'] : null;
    }

    public function markSuccess(int $deliveryId): void
    {
        $this->db->table($this->table)
            ->where('id', $deliveryId)
            ->update([
                'status' => self::STATUS_SUCCESS,
                'processed_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function markExpired(int $deliveryId): void
    {
        $this->db->table($this->table)
            ->where('id', $deliveryId)
            ->update([
                'status' => self::STATUS_EXPIRED,
                'processed_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function markFailed(int $deliveryId, string $errorCode): void
    {
        $this->db->table($this->table)
            ->where('id', $deliveryId)
            ->update([
                'status' => self::STATUS_FAILED,
                'processed_at' => date('Y-m-d H:i:s'),
                'last_error' => $errorCode,
            ]);
    }

    public function markRetry(int $deliveryId, string $errorCode): void
    {
        $delivery = $this->find($deliveryId);
        if (!$delivery) {
            return;
        }

        $attempts = (int) ($delivery['attempts'] ?? 0) + 1;
        $status = $attempts >= self::MAX_ATTEMPTS ? self::STATUS_FAILED : self::STATUS_RETRY;
        $backoff = min(60 * (2 ** max($attempts - 1, 0)), 3600);

        $this->db->table($this->table)
            ->where('id', $deliveryId)
            ->update([
                'status' => $status,
                'attempts' => $attempts,
                'available_at' => date('Y-m-d H:i:s', time() + $backoff),
                'last_error' => $errorCode,
            ]);
    }
}
