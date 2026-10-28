<?php

namespace Modules\Communications\Channels;

use App\Models\SentMessage;
use Modules\Communications\Exceptions\ConsentMissing;
use Modules\Communications\Support\WhatsAppConsents;

class WhatsAppDriver implements ChannelDriver
{
    public function __construct(private readonly WhatsAppGateway $gateway, private readonly WhatsAppConsents $consents) {}

    public function deliver(SentMessage $message): void
    {
        if (! $this->consents->isGranted($message->recipient)) {
            throw new ConsentMissing($message->recipient);
        }

        $this->gateway->send($this->consents->normalize($message->recipient), $message->body);
    }
}
