<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationOutbox;

class NotificationService
{
    private Notification $notificationModel;
    private NotificationRecipientResolver $resolver;
    private PushPayloadFactory $payloadFactory;

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->resolver = new NotificationRecipientResolver();
        $this->payloadFactory = new PushPayloadFactory();
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

            $targetUrl = "/gastos/{$expenseId}";

            $notificationId = $this->notificationModel->insert([
                'user_id' => $recipientId,
                'event_type' => 'expense.created',
                'title' => $title,
                'body' => $body,
                'target_url' => $targetUrl,
            ]);

            if ($notificationId) {
                $outboxModel = new NotificationOutbox();
                $outboxModel->createForNotification($notificationId);
            }
        }
    }
}
