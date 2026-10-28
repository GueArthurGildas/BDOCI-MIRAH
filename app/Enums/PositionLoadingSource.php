<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum PositionLoadingSource: string
{
    use HasTranslatedLabel;

    case Api = 'api';
    case Import = 'import';
    case Scraping = 'scraping';
}
