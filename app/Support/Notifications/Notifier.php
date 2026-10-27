<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Support\Authorization\Permission;

final class Notifier
{
    public function notifyPermissionHolders(Permission $permission, Alert $alert, int $cooldownHours): int
    {
        $notified = 0;

        User::permission($permission->value)
            ->where('is_active', true)
            ->each(function (User $user) use ($alert, $cooldownHours, &$notified) {
                if ($this->wasRecentlyNotified($user, $alert->key(), $cooldownHours)) {
                    return;
                }

                $user->notify($alert);
                $notified++;
            });

        return $notified;
    }

    private function wasRecentlyNotified(User $user, string $key, int $cooldownHours): bool
    {
        return $user->notifications()
            ->where('data->key', $key)
            ->where('created_at', '>=', now()->subHours($cooldownHours))
            ->exists();
    }
}
