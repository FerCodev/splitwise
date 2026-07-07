<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class GroupInvitation extends Model
{
    protected $table = 'group_invitations';
    protected $allowedFields = ['group_id', 'invited_by_id', 'email', 'token_hash', 'status', 'expires_at', 'accepted_by_id', 'accepted_at', 'cancelled_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function createInvitation(int $groupId, int $inviterId, string $email): array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Ingresá un email válido.');
        if ((new User())->where('email', $email)->first()) {
            throw new RuntimeException('Ese email ya pertenece a un usuario. Agregalo como amigo primero.');
        }
        $existing = $this->where('group_id', $groupId)->where('email', $email)->where('status', 'pending')->first();
        if ($existing && strtotime($existing['expires_at']) > time()) {
            throw new RuntimeException('Ya existe una invitación pendiente para ese email.');
        }
        if ($existing) $this->update((int) $existing['id'], ['status' => 'expired']);

        $token = bin2hex(random_bytes(32));
        $id = $this->insert([
            'group_id' => $groupId, 'invited_by_id' => $inviterId, 'email' => $email,
            'token_hash' => hash('sha256', $token), 'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);
        if (!$id) throw new RuntimeException('No se pudo crear la invitación.');
        return ['id' => (int) $id, 'token' => $token, 'email' => $email];
    }

    public function validateToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
        return $this->select('group_invitations.*, grupos.nombre group_name, inviter.name inviter_name')
            ->join('grupos', 'grupos.id = group_invitations.group_id')
            ->join('users inviter', 'inviter.id = group_invitations.invited_by_id')
            ->where('token_hash', hash('sha256', $token))->where('status', 'pending')
            ->where('expires_at >', date('Y-m-d H:i:s'))->first();
    }

    public function pendingForGroup(int $groupId): array
    {
        return $this->where('group_id', $groupId)->where('status', 'pending')->orderBy('created_at', 'DESC')->findAll();
    }

    public function cancelInvitation(int $id, int $groupId): bool
    {
        $row = $this->where('id', $id)->where('group_id', $groupId)->where('status', 'pending')->first();
        return $row ? $this->update($id, ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')]) : false;
    }

    public function renew(int $id, int $groupId): array
    {
        $row = $this->where('id', $id)->where('group_id', $groupId)->where('status', 'pending')->first();
        if (!$row) throw new RuntimeException('La invitación ya no está disponible.');
        if ((new User())->where('email', $row['email'])->first()) throw new RuntimeException('Ese email ya se registró en la app.');
        $token = bin2hex(random_bytes(32));
        $this->update($id, ['token_hash' => hash('sha256', $token), 'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))]);
        return ['token' => $token, 'email' => $row['email']];
    }
}
