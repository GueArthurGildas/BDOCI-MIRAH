<?php

namespace App\Models;

use App\Enums\EezCheckResult;
use App\Enums\EezCheckType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['eez_declaration_id', 'check_type', 'result', 'comment'])]
class EezDeclarationCheck extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'check_type' => EezCheckType::class,
            'result' => EezCheckResult::class,
        ];
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(EezDeclaration::class, 'eez_declaration_id');
    }
}
