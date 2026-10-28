<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum NoteType: string
{
    use HasCommunicationsLabel;

    case ToCheck = 'to_check';
    case Suspect = 'suspect';
    case Reminder = 'reminder';
    case ToForward = 'to_forward';
}
