<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'username' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'name'],
            'username_confirmed_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'username'],
        ]);
        $used = [];
        $rows = $this->db->table('users')->select('id, name, email')->orderBy('id')->get()->getResultArray();
        foreach ($rows as $row) {
            $source = trim((string) $row['name']) ?: strstr((string) $row['email'], '@', true);
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $source) ?: '';
            $base = strtolower((string) preg_replace('/[^a-zA-Z0-9._]+/', '', $ascii));
            $base = trim($base, '._');
            if (strlen($base) < 3) $base = 'user' . $row['id'];
            $base = substr($base, 0, 30);
            $candidate = $base; $suffix = 2;
            while (isset($used[$candidate])) {
                $tail = (string) $suffix++;
                $candidate = substr($base, 0, 30 - strlen($tail)) . $tail;
            }
            $used[$candidate] = true;
            $this->db->table('users')->where('id', $row['id'])->update(['username' => $candidate]);
        }
        $this->db->query('ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)');
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['username', 'username_confirmed_at']);
    }
}
