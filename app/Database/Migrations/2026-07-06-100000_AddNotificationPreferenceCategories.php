<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotificationPreferenceCategories extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notification_preferences', [
            'payment_events' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'expense_created',
            ],
            'group_events' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'payment_events',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notification_preferences', ['payment_events', 'group_events']);
    }
}
