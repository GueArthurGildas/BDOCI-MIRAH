<?php

namespace Modules\Communications\Notifications;

use App\Models\SentMessage;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class MessageReceivedAlert extends Alert
{
    public function __construct(private readonly SentMessage $message) {}

    public function key(): string
    {
        return "communications.message.{$this->message->id}";
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Info;
    }

    protected function icon(): string
    {
        return 'icon-mail';
    }

    protected function translationKey(): string
    {
        return 'communications::alerts.message_received';
    }

    protected function params(): array
    {
        return ['subject' => (string) ($this->message->subject ?? $this->message->vessel?->name ?? '')];
    }

    protected function target(): array
    {
        return ['communications.messages.show', ['message' => $this->message->id]];
    }
}
