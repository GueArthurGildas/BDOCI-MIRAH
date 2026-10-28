<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum DataSourceMode: string
{
    use HasTranslatedLabel;

    case Api = 'api';
    case Import = 'import';
    case Scraping = 'scraping';
    case DirectRead = 'direct_read';
}
