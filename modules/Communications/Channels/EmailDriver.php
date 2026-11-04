<?php

namespace Modules\Communications\Channels;

use App\Models\EmailConnector;
use App\Models\SentMessage;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailDriver implements ChannelDriver
{
    public function deliver(SentMessage $message): void
    {
        $connector = EmailConnector::active();
        $mailer = $connector?->mailer() ?? config('mail.default');

        $attachments = app(DocumentStore::class)->for($message);

        Mail::mailer($mailer)->raw($message->body, function ($mail) use ($message, $connector, $attachments) {
            $mail->to($message->recipient)->subject((string) $message->subject);

            foreach ($attachments as $document) {
                $mail->attachData(Storage::disk(DocumentStore::DISK)->get($document->path), $document->name, ['mime' => $document->mime_type]);
            }

            if ($connector?->domain) {
                $mail->from('no-reply@'.$connector->domain, config('communications.sender_name'));
            }
        });
    }
}
