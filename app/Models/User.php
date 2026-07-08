<?php

namespace App\Models;

use CodeIgniter\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'password', 'name', 'username', 'username_confirmed_at', 'role', 'color', 'avatar_filename', 'avatar_updated_at'];
    protected $useTimestamps = true;
}
