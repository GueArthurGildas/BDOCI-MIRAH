<?php

namespace Modules\Notifications\Support;

use Illuminate\Notifications\DatabaseNotification;

final class NotificationPresenter
{
    /** @return array{id: string, level: string, icon: string, title: string, message: string, url: string, time: string, read: bool} */
    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $params = $data['params'] ?? [];

        return [
            'id' => $notification->id,
            'level' => $data['level'],
            'icon' => $data['icon'],
            'title' => __("{$data['translation_key']}.title", $params),
            'message' => trans_choice("{$data['translation_key']}.message", $params['count'] ?? 1, $params),
            'url' => route('notifications.open', $notification->id),
            'time' => $notification->created_at->diffForHumans(),
            'read' => $notification->read_at !== null,
        ];
    }
}
