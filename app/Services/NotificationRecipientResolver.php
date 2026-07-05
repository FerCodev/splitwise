<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationOutbox;
use App\Models\Grupo;

class NotificationRecipientResolver
{
    public function resolveForGroup(int $groupId, int $excludeUserId): array
    {
        $grupoModel = new Grupo();
        $miembros = $grupoModel->getMiembros($groupId);

        $recipients = array_filter(
            array_column($miembros, 'user_id'),
            static fn(int $uid) => $uid !== $excludeUserId
        );

        return array_map('intval', array_values($recipients));
    }
}
