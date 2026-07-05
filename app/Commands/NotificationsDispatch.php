<?php

namespace App\Commands;

use App\Services\NotificationDispatcher;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class NotificationsDispatch extends BaseCommand
{
    protected $group = 'Notifications';
    protected $name = 'notifications:dispatch';
    protected $description = 'Procesa los trabajos pendientes de la outbox y envía notificaciones Web Push.';
    protected $usage = 'notifications:dispatch [limit]';

    public function run(array $params)
    {
        $limit = (int) ($params[0] ?? 50);

        $dispatcher = new NotificationDispatcher();
        $result = $dispatcher->dispatch($limit);

        if (isset($result['error'])) {
            CLI::write(CLI::color($result['error'], 'yellow'));
            return;
        }

        CLI::write('Resumen de despacho:', 'bold');
        CLI::write("  Procesadas: {$result['processed']}", 'white');
        CLI::write("  Enviadas:   " . CLI::color((string) $result['sent'], 'green'));
        CLI::write("  Vencidas:   " . ($result['expired'] > 0 ? CLI::color((string) $result['expired'], 'red') : (string) $result['expired']));
        CLI::write("  Reintentadas: " . ($result['retried'] > 0 ? CLI::color((string) $result['retried'], 'yellow') : (string) $result['retried']));
        CLI::write("  Fallidas:   " . ($result['failed'] > 0 ? CLI::color((string) $result['failed'], 'red') : (string) $result['failed']));
    }
}
