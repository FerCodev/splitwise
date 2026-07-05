<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationOutbox;

class NotificationService
{
    private Notification $notificationModel;
    private NotificationRecipientResolver $resolver;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->resolver = new NotificationRecipientResolver();
    }

    public function notifyExpenseCreated(
        int $groupId,
        string $groupName,
        int $actorId,
        string $actorName,
        int $expenseId,
        string $expenseDescription,
        float $expenseAmount
    ): void {
        $recipientIds = $this->resolver->resolveForGroup($groupId, $actorId);

        if (empty($recipientIds)) {
            return;
        }

        $prefModel = new NotificationPreference();
        $outboxModel = new NotificationOutbox();

        foreach ($recipientIds as $recipientId) {
            $prefs = $prefModel->getForUser($recipientId);

            if (empty($prefs['expense_created'])) {
                continue;
            }

            $title = "Nuevo gasto en {$groupName}";

            if (!empty($prefs['show_amounts'])) {
                $body = "{$actorName} cargó \"{$expenseDescription}\" por " . moneda($expenseAmount);
            } else {
                $body = "{$actorName} registró \"{$expenseDescription}\"";
            }

            $targetUrl = base_url("gastos/{$expenseId}");

            $notificationId = $this->notificationModel->insert([
                'user_id' => $recipientId,
                'event_type' => 'expense.created',
                'title' => $title,
                'body' => $body,
                'target_url' => $targetUrl,
            ]);

            if (!$notificationId) {
                throw new \RuntimeException('Error al crear la notificación.');
            }

            if (!$outboxModel->createForNotification($notificationId)) {
                throw new \RuntimeException('Error al crear el registro de outbox.');
            }
        }
    }
}
