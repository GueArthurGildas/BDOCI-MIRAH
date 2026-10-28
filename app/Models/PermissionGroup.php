<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label', 'is_system'])]
class PermissionGroup extends Model
{
    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function displayLabel(): string
    {
        return $this->is_system ? __("administration::groups.system.{$this->code}.label") : (string) $this->label;
    }

    public function description(): ?string
    {
        return $this->is_system ? __("administration::groups.system.{$this->code}.description") : null;
    }

    public function grants(): HasMany
    {
        return $this->hasMany(PermissionGrant::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_group_user')->using(GroupMembership::class)->orderBy('name');
    }
}
