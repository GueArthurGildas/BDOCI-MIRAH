<?php

namespace App\Support\Notifications;

use Illuminate\Notifications\Notification;

abstract class Alert extends Notification
{
    abstract public function key(): string;

    abstract protected function level(): AlertLevel;

    abstract protected function icon(): string;

    abstract protected function translationKey(): string;

    /** @return array<string, scalar> */
    abstract protected function params(): array;

    /** @return array{0: string, 1: array<string, scalar>} */
    abstract protected function target(): array;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'key' => $this->key(),
            'level' => $this->level()->value,
            'icon' => $this->icon(),
            'translation_key' => $this->translationKey(),
            'params' => $this->params(),
            'target' => $this->target(),
        ];
    }
}
