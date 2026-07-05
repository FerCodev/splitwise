<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveriesInitializedAtToOutbox extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notification_outbox', [
            'deliveries_initialized_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_error'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notification_outbox', 'deliveries_initialized_at');
    }
}
