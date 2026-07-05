<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';
    protected $primaryKey = 'id';
    protected $allowedFields = ['notification_id', 'status', 'attempts', 'available_at', 'processed_at', 'last_error'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_RETRY = 'retry';
    const STATUS_FAILED = 'failed';

    const MAX_ATTEMPTS = 5;

    public function getPendingJobs(int $limit = 50): array
    {
        $db = $this->db;

        $allowedStatuses = [self::STATUS_PENDING, self::STATUS_RETRY];

        $rows = $db->query(
            'SELECT * FROM notification_outbox 
             WHERE status IN (\'' . implode("','", $allowedStatuses) . '\') 
             AND available_at <= ? 
             AND attempts < ?
             ORDER BY available_at ASC 
             LIMIT ?',
            [date('Y-m-d H:i:s'), self::MAX_ATTEMPTS, $limit]
        )->getResultArray();

        return $rows;
    }

    public function claimForProcessing(int $id): bool
    {
        return (bool) $this->db->query(
            'UPDATE notification_outbox SET status = ?, attempts = attempts + 1, updated_at = ? 
             WHERE id = ? AND status IN (\'' . self::STATUS_PENDING . "','" . self::STATUS_RETRY . '\') 
             AND attempts < ?',
            [self::STATUS_PROCESSING, date('Y-m-d H:i:s'), $id, self::MAX_ATTEMPTS]
        );
    }

    public function markCompleted(int $id): bool
    {
        return $this->update($id, [
            'status' => self::STATUS_COMPLETED,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markRetry(int $id, string $error): bool
    {
        $job = $this->find($id);
        if (!$job) {
            return false;
        }

        $attempts = (int) ($job['attempts'] ?? 0);
        $status = $attempts >= self::MAX_ATTEMPTS ? self::STATUS_FAILED : self::STATUS_RETRY;

        $backoff = min(60 * pow(2, $attempts - 1), 3600);
        $availableAt = date('Y-m-d H:i:s', time() + $backoff);

        return $this->update($id, [
            'status' => $status,
            'available_at' => $availableAt,
            'last_error' => mb_substr($error, 0, 500),
        ]);
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
