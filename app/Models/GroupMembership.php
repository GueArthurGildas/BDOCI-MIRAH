<?php

namespace App\Models;

use App\Support\Authorization\PermissionResolver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupMembership extends Pivot
{
    public $incrementing = false;

    protected $table = 'permission_group_user';

    protected static function booted(): void
    {
        $flush = fn () => app(PermissionResolver::class)->flush();

        static::created($flush);
        static::deleted($flush);
    }
}
