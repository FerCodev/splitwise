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

    const ALLOWED_ERROR_CODES = [
        'expired',
        'transient_http_429',
        'transient_http_5xx',
        'permanent_http_4xx',
        'invalid_endpoint',
        'transport_error',
        'subscription_gone',
        'pending_future',
        'partial_delivery',
    ];

    public function insertSnapshot(int $notificationId, array $subscriptionIds): int
    {
        if (empty($subscriptionIds)) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($subscriptionIds as $subId) {
            $rows[] = [
                'notification_id' => $notificationId,
                'push_subscription_id' => (int) $subId,
                'status' => self::STATUS_PENDING,
                'attempts' => 0,
                'available_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->table($this->table)->insertBatch($rows);

        return count($rows);
    }

    public function cleanupPartialSnapshot(int $notificationId): void
    {
        $this->db->table($this->table)
            ->where('notification_id', $notificationId)
            ->delete();
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
                'last_error' => $this->sanitizeErrorCode($errorCode),
            ]);
    }

    public function markRetry(int $deliveryId, string $errorCode): void
    {
        $delivery = $this->find($deliveryId);
        if (!$delivery) {
            return;
        }

        $attempts = (int) ($delivery['attempts'] ?? 0) + 1;
        $backoff = min(60 * (2 ** max($attempts - 1, 0)), 3600);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->db->table($this->table)
                ->where('id', $deliveryId)
                ->update([
                    'status' => self::STATUS_FAILED,
                    'attempts' => $attempts,
                    'processed_at' => date('Y-m-d H:i:s'),
                    'last_error' => $this->sanitizeErrorCode($errorCode),
                ]);
        } else {
            $this->db->table($this->table)
                ->where('id', $deliveryId)
                ->update([
                    'status' => self::STATUS_RETRY,
                    'attempts' => $attempts,
                    'available_at' => date('Y-m-d H:i:s', time() + $backoff),
                    'processed_at' => null,
                    'last_error' => $this->sanitizeErrorCode($errorCode),
                ]);
        }
    }

    private function sanitizeErrorCode(string $code): string
    {
        $code = trim($code);
        if (!in_array($code, self::ALLOWED_ERROR_CODES, true)) {
            $code = 'unknown';
        }
        return mb_substr($code, 0, 50);
    }
}
