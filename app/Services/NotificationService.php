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
        int $groupId, string $groupName, int $actorId, string $actorName,
        int $expenseId, string $expenseDescription, float $expenseAmount
    ): void {
        $this->notifyUsers(
            $this->resolver->resolveForGroup($groupId, $actorId),
            'expense_created',
            'expense.created',
            "Nuevo gasto en {$groupName}",
            static fn(array $prefs): string => !empty($prefs['show_amounts'])
                ? "{$actorName} cargó \"{$expenseDescription}\" por " . moneda($expenseAmount)
                : "{$actorName} registró \"{$expenseDescription}\"",
            base_url("gastos/{$expenseId}")
        );
    }

    public function notifyExpenseUpdated(
        string $groupName, int $actorId, string $actorName, int $expenseId,
        string $expenseDescription, float $expenseAmount, array $affectedUserIds
    ): void {
        $this->notifyUsers(
            $this->withoutActor($affectedUserIds, $actorId),
            'expense_created',
            'expense.updated',
            "Gasto actualizado en {$groupName}",
            static fn(array $prefs): string => !empty($prefs['show_amounts'])
                ? "{$actorName} actualizó \"{$expenseDescription}\" a " . moneda($expenseAmount)
                : "{$actorName} actualizó \"{$expenseDescription}\"",
            base_url("gastos/{$expenseId}")
        );
    }

    public function notifyExpenseDeleted(
        int $groupId, string $groupName, int $actorId, string $actorName,
        string $expenseDescription, float $expenseAmount, array $affectedUserIds
    ): void {
        $this->notifyUsers(
            $this->withoutActor($affectedUserIds, $actorId),
            'expense_created',
            'expense.deleted',
            "Gasto eliminado en {$groupName}",
            static fn(array $prefs): string => !empty($prefs['show_amounts'])
                ? "{$actorName} eliminó \"{$expenseDescription}\" por " . moneda($expenseAmount)
                : "{$actorName} eliminó \"{$expenseDescription}\"",
            base_url("grupos/{$groupId}")
        );
    }

    public function notifyPaymentCreated(
        string $groupName, int $actorId, string $actorName, int $paymentId,
        int $payerId, int $receiverId, float $amount
    ): void {
        $this->notifyUsers(
            $this->withoutActor([$payerId, $receiverId], $actorId),
            'payment_events',
            'payment.created',
            "Pago registrado en {$groupName}",
            static fn(array $prefs): string => !empty($prefs['show_amounts'])
                ? "{$actorName} registró un pago de " . moneda($amount)
                : "{$actorName} registró un pago",
            base_url("pagos/{$paymentId}")
        );
    }

    public function notifyMemberAdded(int $groupId, string $groupName, int $userId, string $actorName): void
    {
        $this->notifyUsers(
            [$userId], 'group_events', 'group.member_added',
            "Te agregaron a {$groupName}",
            static fn(array $prefs): string => "{$actorName} te agregó al grupo",
            base_url("grupos/{$groupId}")
        );
    }

    public function notifyMemberRemoved(string $groupName, int $userId, string $actorName): void
    {
        $this->notifyUsers(
            [$userId], 'group_events', 'group.member_removed',
            "Ya no pertenecés a {$groupName}",
            static fn(array $prefs): string => "{$actorName} te quitó del grupo",
            base_url('dashboard')
        );
    }

    public function notifyGroupStateChanged(
        int $groupId, string $groupName, int $actorId, string $actorName,
        string $state, array $debts = []
    ): void {
        $debtsByUser = [];
        foreach ($debts as $debt) {
            $debtorId = (int) ($debt['deudor_id'] ?? 0);
            if ($debtorId > 0) {
                $debtsByUser[$debtorId] = ($debtsByUser[$debtorId] ?? 0.0) + (float) ($debt['monto'] ?? 0);
            }
        }

        $titles = [
            'cerrado' => "Grupo cerrado: {$groupName}",
            'activo' => "Grupo reabierto: {$groupName}",
            'liquidado' => "Grupo liquidado: {$groupName}",
        ];

        foreach ($this->resolver->resolveForGroup($groupId, 0) as $recipientId) {
            $debtAmount = $state === 'cerrado' ? ($debtsByUser[$recipientId] ?? 0.0) : 0.0;
            $this->notifyUsers(
                [$recipientId], 'group_events', 'group.state_changed',
                $titles[$state] ?? "Estado actualizado: {$groupName}",
                static function (array $prefs) use ($actorName, $state, $debtAmount): string {
                    if ($debtAmount > 0) {
                        return !empty($prefs['show_amounts'])
                            ? 'El grupo se cerró. Tenés pagos pendientes por ' . moneda($debtAmount)
                            : 'El grupo se cerró. Tenés pagos pendientes por realizar';
                    }
                    return match ($state) {
                        'cerrado' => "{$actorName} cerró el grupo",
                        'activo' => "{$actorName} reabrió el grupo",
                        'liquidado' => "{$actorName} liquidó el grupo",
                        default => "{$actorName} actualizó el estado del grupo",
                    };
                },
                $debtAmount > 0 ? base_url("grupos/{$groupId}/balance") : base_url("grupos/{$groupId}")
            );
        }
    }

    private function withoutActor(array $userIds, int $actorId): array
    {
        return array_values(array_filter(
            array_unique(array_map('intval', $userIds)),
            static fn(int $userId): bool => $userId > 0 && $userId !== $actorId
        ));
    }

    private function notifyUsers(
        array $recipientIds, string $preferenceKey, string $eventType,
        string $title, callable $bodyBuilder, string $targetUrl
    ): void {
        $recipientIds = array_values(array_unique(array_filter(array_map('intval', $recipientIds))));
        if (empty($recipientIds)) {
            return;
        }

        $prefModel = new NotificationPreference();
        $outboxModel = new NotificationOutbox();

        foreach ($recipientIds as $recipientId) {
            $prefs = $prefModel->getForUser($recipientId);
            if (empty($prefs[$preferenceKey])) {
                continue;
            }

            $notificationId = $this->notificationModel->insert([
                'user_id' => $recipientId,
                'event_type' => $eventType,
                'title' => $title,
                'body' => $bodyBuilder($prefs),
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
