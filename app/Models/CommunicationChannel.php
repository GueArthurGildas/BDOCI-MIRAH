<?php

namespace App\Models;

use App\Enums\CommunicationChannelCode;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'is_auto_dispatch_enabled', 'is_consent_required'])]
class CommunicationChannel extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'code' => CommunicationChannelCode::class,
            'is_auto_dispatch_enabled' => 'boolean',
            'is_consent_required' => 'boolean',
        ];
    }

    public static function forCode(CommunicationChannelCode $code): self
    {
        return self::firstOrCreate(['code' => $code->value], [
            'is_auto_dispatch_enabled' => $code === CommunicationChannelCode::InApp,
            'is_consent_required' => $code === CommunicationChannelCode::WhatsApp,
        ]);
    }
}
