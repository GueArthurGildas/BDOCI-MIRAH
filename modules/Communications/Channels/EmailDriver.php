<?php

namespace Modules\Communications\Channels;

use App\Models\EmailConnector;
use App\Models\SentMessage;
use Illuminate\Support\Facades\Mail;

class EmailDriver implements ChannelDriver
{
    public function deliver(SentMessage $message): void
    {
        $connector = EmailConnector::active();
        $mailer = $connector?->mailer() ?? config('mail.default');

        Mail::mailer($mailer)->raw($message->body, function ($mail) use ($message, $connector) {
            $mail->to($message->recipient)->subject((string) $message->subject);

            if ($connector?->domain) {
                $mail->from('no-reply@'.$connector->domain, config('communications.sender_name'));
            }
        });
    }
}
