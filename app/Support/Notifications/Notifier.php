<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionResolver;

final class Notifier
{
    public function notifyPermissionHolders(Permission $permission, Alert $alert, int $cooldownHours, ?User $except = null): int
    {
        $notified = 0;

        app(PermissionResolver::class)->holders($permission->value)
            ->reject(fn (User $user) => $user->is($except))
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
