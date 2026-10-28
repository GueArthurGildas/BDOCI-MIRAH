<?php

namespace Modules\Communications\Channels;

use App\Models\SentMessage;

interface ChannelDriver
{
    public function deliver(SentMessage $message): void;
}
