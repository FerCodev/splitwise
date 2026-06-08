<?php

namespace App\Models;

use CodeIgniter\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'password', 'name', 'role'];
    protected $useTimestamps = true;

    public static function isAdmin(array $user): bool
    {
        return ($user['role'] ?? 'user') === 'admin';
    }

    public static function hasRole(array $user, string $role): bool
    {
        return ($user['role'] ?? 'user') === $role;
    }

    public function getRole(int $userId): string
    {
        $row = $this->select('role')->find($userId);
        return $row ? ($row['role'] ?? 'user') : 'user';
    }
}
