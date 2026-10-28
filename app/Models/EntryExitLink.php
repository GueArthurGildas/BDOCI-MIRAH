<?php

namespace App\Models;

use App\Enums\EntryExitExplanation;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entry_declaration_id', 'exit_declaration_id', 'variance_kg', 'explanation'])]
class EntryExitLink extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'variance_kg' => 'float',
            'explanation' => EntryExitExplanation::class,
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(EezDeclaration::class, 'entry_declaration_id');
    }

    public function exit(): BelongsTo
    {
        return $this->belongsTo(EezDeclaration::class, 'exit_declaration_id');
    }
}
