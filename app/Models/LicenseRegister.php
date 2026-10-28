<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['year', 'document_id', 'generated_at'])]
class LicenseRegister extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'year';

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
