<?php

namespace App\Models;

use CodeIgniter\Model;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'endpoint', 'endpoint_hash', 'public_key', 'auth_token', 'content_encoding', 'user_agent', 'enabled', 'last_success_at', 'last_failure_at', 'failure_count'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $returnType = 'array';

    public function findByEndpointHash(string $hash): ?array
    {
        return $this->where('endpoint_hash', $hash)->first();
    }

    public function upsertForUser(int $userId, array $subscriptionData): bool
    {
        $endpoint = $subscriptionData['endpoint'];
        $hash = hash('sha256', $endpoint);
        $existing = $this->findByEndpointHash($hash);

        $data = [
            'endpoint' => $endpoint,
            'endpoint_hash' => $hash,
            'public_key' => $subscriptionData['keys']['p256dh'] ?? '',
            'auth_token' => $subscriptionData['keys']['auth'] ?? '',
            'content_encoding' => $subscriptionData['content_encoding'] ?? 'aes128gcm',
            'user_agent' => $subscriptionData['user_agent'] ?? null,
            'enabled' => 1,
            'failure_count' => 0,
        ];

        if ($existing) {
            $data['user_id'] = $userId;
            return $this->update($existing['id'], $data);
        }

        $data['user_id'] = $userId;
        return (bool) $this->insert($data);
    }

    public function findEnabledByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('enabled', 1)
            ->findAll();
    }

    public function disable(int $id, int $userId): bool
    {
        return $this->where('id', $id)->where('user_id', $userId)->set('enabled', 0)->update();
    }

    public function recordSuccess(int $id): bool
    {
        return $this->update($id, [
            'last_success_at' => date('Y-m-d H:i:s'),
            'failure_count' => 0,
        ]);
    }

    public function recordFailure(int $id): bool
    {
        $sub = $this->find($id);
        if (!$sub) {
            return false;
        }

        return $this->update($id, [
            'last_failure_at' => date('Y-m-d H:i:s'),
            'failure_count' => ((int) ($sub['failure_count'] ?? 0)) + 1,
        ]);
    }
}
