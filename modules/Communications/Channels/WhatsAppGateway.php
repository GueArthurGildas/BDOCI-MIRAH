<?php

namespace Modules\Communications\Channels;

interface WhatsAppGateway
{
    public function send(string $number, string $text): void;
}
