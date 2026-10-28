<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum OperationCategoryCode: string
{
    use HasTranslatedLabel;

    case Vms = 'vms';
    case Ers = 'ers';
    case Mep = 'mep';
    case Inn = 'inn';
    case Div = 'div';
}
