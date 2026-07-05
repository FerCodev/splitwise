<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';
    protected $primaryKey = 'id';
    protected $allowedFields = ['notification_id', 'status', 'attempts', 'available_at', 'processed_at', 'last_error', 'deliveries_initialized_at'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_RETRY = 'retry';
    const STATUS_FAILED = 'failed';

    const MAX_ATTEMPTS = 100;
    const ORPHAN_TIMEOUT_MINUTES = 10;

    const ALLOWED_ERROR_CODES = [
        'notification_not_found',
        'pending_future',
        'partial_delivery',
        'transport_error',
        'delivery_initialization_failed',
        'unknown',
    ];

    public function getPendingJobs(int $limit = 50): array
    {
        $db = $this->db;
        $allowedStatuses = [self::STATUS_PENDING, self::STATUS_RETRY];

        return $db->query(
            'SELECT * FROM notification_outbox WHERE status IN (\'' . implode("','", $allowedStatuses) . '\') AND available_at <= ? AND attempts < ? ORDER BY available_at ASC LIMIT ?',
            [date('Y-m-d H:i:s'), self::MAX_ATTEMPTS, $limit]
        )->getResultArray();
    }

    public function claimForProcessing(int $id): bool
    {
        $result = $this->db->query(
            'UPDATE notification_outbox SET status = ?, attempts = attempts + 1, updated_at = ? WHERE id = ? AND status IN (\'' . self::STATUS_PENDING . "','" . self::STATUS_RETRY . '\') AND attempts < ?',
            [self::STATUS_PROCESSING, date('Y-m-d H:i:s'), $id, self::MAX_ATTEMPTS]
        );

        return $this->db->affectedRows() === 1;
    }

    public function recoverOrphanJobs(): int
    {
        $threshold = date('Y-m-d H:i:s', time() - (self::ORPHAN_TIMEOUT_MINUTES * 60));

        $this->db->query(
            'UPDATE notification_outbox SET status = ?, updated_at = ? WHERE status = ? AND updated_at <= ? AND attempts >= ?',
            [self::STATUS_FAILED, date('Y-m-d H:i:s'), self::STATUS_PROCESSING, $threshold, self::MAX_ATTEMPTS]
        );
        $failed = $this->db->affectedRows();

        $this->db->query(
            'UPDATE notification_outbox SET status = ?, available_at = ?, updated_at = ? WHERE status = ? AND updated_at <= ? AND attempts < ?',
            [self::STATUS_RETRY, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), self::STATUS_PROCESSING, $threshold, self::MAX_ATTEMPTS]
        );
        $retried = $this->db->affectedRows();

        return $failed + $retried;
    }

    public function markCompleted(int $id): bool
    {
        return $this->update($id, [
            'status' => self::STATUS_COMPLETED,
            'processed_at' => date('Y-m-d H:i:s'),
            'last_error' => null,
        ]);
    }

    public function markRetry(int $id, string $errorCode): bool
    {
        $job = $this->find($id);
        if (!$job) {
            return false;
        }

        $attempts = (int) ($job['attempts'] ?? 0);
        $status = $attempts >= self::MAX_ATTEMPTS ? self::STATUS_FAILED : self::STATUS_RETRY;

        $backoff = min(60 * (2 ** ($attempts - 1)), 3600);
        $availableAt = date('Y-m-d H:i:s', time() + max($backoff, 0));

        $data = [
            'status' => $status,
            'available_at' => $availableAt,
            'processed_at' => null,
            'last_error' => $this->sanitizeErrorCode($errorCode),
        ];

        if ($status === self::STATUS_FAILED) {
            $data['processed_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $data);
    }

    public function scheduleRetry(int $id, string $errorCode, string $availableAt): bool
    {
        return $this->update($id, [
            'status' => self::STATUS_RETRY,
            'available_at' => $availableAt,
            'processed_at' => null,
            'last_error' => $this->sanitizeErrorCode($errorCode),
        ]);
    }

    public function markFailed(int $id, string $errorCode): bool
    {
        return $this->update($id, [
            'status' => self::STATUS_FAILED,
            'processed_at' => date('Y-m-d H:i:s'),
            'last_error' => $this->sanitizeErrorCode($errorCode),
        ]);
    }

    public function markDeliveriesInitialized(int $id): bool
    {
        return $this->update($id, [
            'deliveries_initialized_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function isDeliveriesInitialized(array $job): bool
    {
        return !empty($job['deliveries_initialized_at']);
    }

    private function sanitizeErrorCode(string $code): string
    {
        $code = trim($code);
        if (!in_array($code, self::ALLOWED_ERROR_CODES, true)) {
            $code = 'unknown';
        }
        return mb_substr($code, 0, 500);
    }

    public function createForNotification(int $notificationId, string $status = self::STATUS_PENDING): bool
    {
        return (bool) $this->insert([
            'notification_id' => $notificationId,
            'status' => $status,
            'attempts' => 0,
            'available_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
