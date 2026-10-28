<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['supplier', 'domain', 'parameters', 'is_active'])]
class EmailConnector extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return ['parameters' => 'array', 'is_active' => 'boolean'];
    }

    public static function active(): ?self
    {
        return self::where('is_active', true)->latest('id')->first();
    }

    public function mailer(): string
    {
        return $this->parameters['mailer'] ?? config('mail.default');
    }
}
