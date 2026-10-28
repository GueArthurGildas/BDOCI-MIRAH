<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum CustomFieldType: string
{
    use HasTranslatedLabel;

    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Boolean = 'boolean';
    case List = 'list';
}
