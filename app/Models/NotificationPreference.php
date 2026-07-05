<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_id', 'push_enabled', 'expense_created', 'show_amounts'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    public function getForUser(int $userId): array
    {
        $row = $this->find($userId);

        if (!$row) {
            return [
                'user_id' => $userId,
                'push_enabled' => true,
                'expense_created' => true,
                'show_amounts' => true,
            ];
        }

        return $row;
    }

    public function saveForUser(int $userId, array $data): bool
    {
        $exists = $this->find($userId);

        $allowed = ['push_enabled', 'expense_created', 'show_amounts'];
        $filtered = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $filtered[$key] = (bool) $data[$key];
            }
        }

        if (empty($filtered)) {
            return false;
        }

        if ($exists) {
            return $this->update($userId, $filtered);
        }

        $filtered['user_id'] = $userId;
        return (bool) $this->insert($filtered);
    }
}
