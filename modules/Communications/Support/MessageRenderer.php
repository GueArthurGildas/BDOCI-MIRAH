<?php

namespace Modules\Communications\Support;

use App\Models\MessageTemplate;
use App\Models\Vessel;

class MessageRenderer
{
    /** @return array{subject: ?string, body: string} */
    public function render(MessageTemplate $template, ?Vessel $vessel): array
    {
        $variables = $this->variables($vessel);

        return [
            'subject' => $template->subject === null ? null : strtr($template->subject, $variables),
            'body' => strtr($template->body, $variables),
        ];
    }

    /** @return array<string, string> */
    public function variables(?Vessel $vessel): array
    {
        return [
            '{vessel_name}' => $vessel?->name ?? '',
            '{registration_number}' => $vessel?->registration_number ?? '',
            '{call_sign}' => (string) $vessel?->call_sign,
            '{owner}' => (string) $vessel?->owner,
            '{date}' => now()->toDateString(),
            '{sender}' => config('communications.sender_name'),
        ];
    }
}
