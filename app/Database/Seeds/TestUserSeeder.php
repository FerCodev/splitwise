<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['email' => 'antonella@test.com', 'name' => 'Antonella'],
            ['email' => 'fernando@test.com', 'name' => 'Fernando'],
        ];

        foreach ($users as $user) {
            $exists = $this->db->table('users')
                ->where('email', $user['email'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('users')->insert([
                    'email' => $user['email'],
                    'password' => password_hash('1234', PASSWORD_DEFAULT),
                    'name' => $user['name'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
