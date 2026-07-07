<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class Friendship extends Model
{
    protected $table = 'friendships';
    protected $allowedFields = ['user_low_id', 'user_high_id', 'requested_by_id', 'status', 'responded_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    private function pair(int $a, int $b): array
    {
        return [min($a, $b), max($a, $b)];
    }

    public function between(int $a, int $b): ?array
    {
        [$low, $high] = $this->pair($a, $b);
        return $this->where('user_low_id', $low)->where('user_high_id', $high)->first();
    }

    public function request(int $from, int $to): void
    {
        if ($from <= 0 || $to <= 0 || $from === $to) {
            throw new RuntimeException('No podés enviarte una solicitud a vos mismo.');
        }
        $users = (new User())->whereIn('id', [$from, $to])->where('role !=', 'admin')->findAll();
        if (count($users) !== 2) {
            throw new RuntimeException('El usuario no está disponible para solicitudes.');
        }
        [$low, $high] = $this->pair($from, $to);
        $existing = $this->between($from, $to);
        if ($existing && $existing['status'] !== 'rejected') {
            throw new RuntimeException($existing['status'] === 'accepted' ? 'Ya son amigos.' : 'Ya existe una solicitud pendiente.');
        }
        $data = ['user_low_id' => $low, 'user_high_id' => $high, 'requested_by_id' => $from, 'status' => 'pending', 'responded_at' => null];
        try {
            $ok = $existing ? $this->update((int) $existing['id'], $data) : $this->insert($data);
        } catch (\Throwable) {
            throw new RuntimeException('Ya existe una solicitud para estos usuarios.');
        }
        if (!$ok) throw new RuntimeException('No se pudo enviar la solicitud.');
    }

    public function respond(int $id, int $userId, bool $accept): array
    {
        $row = $this->find($id);
        if (!$row || $row['status'] !== 'pending' || (int) $row['requested_by_id'] === $userId
            || !in_array($userId, [(int) $row['user_low_id'], (int) $row['user_high_id']], true)) {
            throw new RuntimeException('La solicitud ya no está disponible.');
        }
        $status = $accept ? 'accepted' : 'rejected';
        if (!$this->update($id, ['status' => $status, 'responded_at' => date('Y-m-d H:i:s')])) {
            throw new RuntimeException('No se pudo actualizar la solicitud.');
        }
        $row['status'] = $status;
        return $row;
    }

    public function cancel(int $id, int $userId): bool
    {
        $row = $this->find($id);
        return $row && $row['status'] === 'pending' && (int) $row['requested_by_id'] === $userId
            ? $this->delete($id) : false;
    }

    public function unfriend(int $id, int $userId): bool
    {
        $row = $this->find($id);
        return $row && $row['status'] === 'accepted'
            && in_array($userId, [(int) $row['user_low_id'], (int) $row['user_high_id']], true)
            ? $this->delete($id) : false;
    }

    public function dashboard(int $userId): array
    {
        $rows = $this->select('friendships.*, low.name low_name, low.avatar_filename low_avatar_filename, low.avatar_updated_at low_avatar_updated_at, high.name high_name, high.avatar_filename high_avatar_filename, high.avatar_updated_at high_avatar_updated_at')
            ->join('users low', 'low.id = friendships.user_low_id')->join('users high', 'high.id = friendships.user_high_id')
            ->groupStart()->where('user_low_id', $userId)->orWhere('user_high_id', $userId)->groupEnd()
            ->orderBy('friendships.updated_at', 'DESC')->findAll();
        $result = ['friends' => [], 'received' => [], 'sent' => []];
        foreach ($rows as $row) {
            $otherLow = (int) $row['user_low_id'] !== $userId;
            $row['other_id'] = $otherLow ? $row['user_low_id'] : $row['user_high_id'];
            $row['other_name'] = $otherLow ? $row['low_name'] : $row['high_name'];
            $row['other_avatar_filename'] = $otherLow ? $row['low_avatar_filename'] : $row['high_avatar_filename'];
            $row['other_avatar_updated_at'] = $otherLow ? $row['low_avatar_updated_at'] : $row['high_avatar_updated_at'];
            if ($row['status'] === 'accepted') $result['friends'][] = $row;
            elseif ($row['status'] === 'pending') $result[(int) $row['requested_by_id'] === $userId ? 'sent' : 'received'][] = $row;
        }
        return $result;
    }
}
