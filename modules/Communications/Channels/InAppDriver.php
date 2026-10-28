<?php

namespace Modules\Communications\Channels;

use App\Models\SentMessage;
use App\Models\User;
use Modules\Communications\Exceptions\RecipientUnknown;
use Modules\Communications\Notifications\MessageReceivedAlert;

class InAppDriver implements ChannelDriver
{
    public function deliver(SentMessage $message): void
    {
        $user = User::where('email', $message->recipient)->where('is_active', true)->first()
            ?? throw new RecipientUnknown($message->recipient);

        $user->notify(new MessageReceivedAlert($message));
    }
}
