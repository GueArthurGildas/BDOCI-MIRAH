<?php

namespace Modules\Communications\Channels;

use Illuminate\Support\Facades\Log;

class FakeWhatsAppGateway implements WhatsAppGateway
{
    /** @var array<int, array{number: string, text: string}> */
    private array $sent = [];

    public function send(string $number, string $text): void
    {
        $this->sent[] = ['number' => $number, 'text' => $text];

        Log::info('WhatsApp message recorded by the fake gateway', ['number' => $number]);
    }

    /** @return array<int, array{number: string, text: string}> */
    public function sent(): array
    {
        return $this->sent;
    }
}
