<?php

namespace App\Models;

use App\Enums\ExternalLinkSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['source', 'model_url', 'is_copy_fallback'])]
class ExternalLink extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'source' => ExternalLinkSource::class,
            'is_copy_fallback' => 'boolean',
        ];
    }

    public static function forSource(ExternalLinkSource $source): ?self
    {
        return self::where('source', $source->value)->first();
    }

    public function urlFor(?string $externalId): ?string
    {
        if (blank($this->model_url) || blank($externalId)) {
            return null;
        }

        return str_replace('{id}', rawurlencode($externalId), $this->model_url);
    }
}
