<?php

namespace App\Models;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageForm;
use App\Enums\MessageLanguage;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'channel', 'language', 'subject', 'body', 'form', 'is_active'])]
class MessageTemplate extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannelCode::class,
            'language' => MessageLanguage::class,
            'form' => MessageForm::class,
            'is_active' => 'boolean',
        ];
    }

    public function dispatchRules(): HasMany
    {
        return $this->hasMany(DispatchRule::class);
    }
}
