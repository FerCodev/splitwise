<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    protected function queueNotifications(?callable $queue = null): void
    {
        try {
            if ($queue !== null) {
                $queue(new \App\Services\NotificationService());
            }
            (new \App\Services\NotificationDispatcher())->dispatch();
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo procesar una notificación: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
