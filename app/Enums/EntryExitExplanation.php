<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EntryExitExplanation: string implements HasBadge
{
    use HasTranslatedLabel;

    case CatchDocument = 'catch_document';
    case Transshipment = 'transshipment';
    case Landing = 'landing';
    case Unexplained = 'unexplained';

    public function color(): string
    {
        return match ($this) {
            self::CatchDocument => 'success',
            self::Transshipment => 'info',
            self::Landing => 'info',
            self::Unexplained => 'danger',
        };
    }
}
