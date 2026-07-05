<?php

namespace App\Models;

use CodeIgniter\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'event_type', 'title', 'body', 'target_url', 'read_at'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    public function getByUser(int $userId, int $perPage = 20): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('read_at', null)
            ->countAllResults();
    }

    public function markAsRead(int $id, int $userId): bool
    {
        $notification = $this->where('id', $id)->where('user_id', $userId)->first();
        if (!$notification || $notification['read_at'] !== null) {
            return !$notification ? false : true;
        }

        return $this->update($id, ['read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('read_at', null)
            ->set('read_at', date('Y-m-d H:i:s'))
            ->update();
    }

    public function belongsToUser(int $id, int $userId): bool
    {
        return $this->where('id', $id)->where('user_id', $userId)->countAllResults() > 0;
    }
}
