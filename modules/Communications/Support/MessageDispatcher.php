<?php

namespace Modules\Communications\Support;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageStatus;
use App\Models\CommunicationChannel;
use App\Models\SentMessage;
use Illuminate\Validation\ValidationException;
use Modules\Communications\Channels\ChannelDriver;
use Modules\Communications\Channels\EmailDriver;
use Modules\Communications\Channels\InAppDriver;
use Modules\Communications\Channels\WhatsAppDriver;
use Throwable;

class MessageDispatcher
{
    public function __construct(private readonly WhatsAppConsents $consents) {}

    /** @param array<string, mixed> $attributes */
    public function prepare(CommunicationChannelCode $channel, array $attributes): SentMessage
    {
        return SentMessage::create([
            ...$attributes,
            'communication_channel_id' => CommunicationChannel::forCode($channel)->id,
            'status' => MessageStatus::Prepared->value,
        ]);
    }

    public function deliver(SentMessage $message): SentMessage
    {
        try {
            $this->driver($message->channel->code)->deliver($message);
            $message->update(['status' => MessageStatus::Sent->value, 'sent_at' => now()]);
        } catch (Throwable $exception) {
            report($exception);
            $message->update(['status' => MessageStatus::Failed->value]);
        }

        return $message;
    }

    public function canDeliver(CommunicationChannelCode $channel, string $recipient): bool
    {
        return $channel !== CommunicationChannelCode::WhatsApp
            || ! CommunicationChannel::forCode($channel)->is_consent_required
            || $this->consents->isGranted($recipient);
    }

    public function assertCanDeliver(CommunicationChannelCode $channel, string $recipient): void
    {
        if (! $this->canDeliver($channel, $recipient)) {
            throw ValidationException::withMessages(['recipient' => __('communications::messages.consent_missing')]);
        }
    }

    private function driver(CommunicationChannelCode $channel): ChannelDriver
    {
        return match ($channel) {
            CommunicationChannelCode::InApp => app(InAppDriver::class),
            CommunicationChannelCode::Email => app(EmailDriver::class),
            CommunicationChannelCode::WhatsApp => app(WhatsAppDriver::class),
        };
    }
}
