<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum DataSourceKind: string
{
    use HasTranslatedLabel;

    case Api = 'api';
    case Database = 'database';
    case File = 'file';
}
