<?php

namespace App\Models;

use App\Support\Authorization\PermissionResolver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleMembership extends Pivot
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'role_user';

    protected static function booted(): void
    {
        $flush = fn () => app(PermissionResolver::class)->flush();

        static::created($flush);
        static::deleted($flush);
    }
}
