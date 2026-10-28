<?php

namespace App\Models\Concerns;

use App\Models\RoleMembership;
use App\Models\RoleRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleRecord::class, 'role_user', 'user_id', 'role_id')->using(RoleMembership::class);
    }

    public function assignRole(RoleRecord|string $role): static
    {
        $this->roles()->syncWithoutDetaching([$this->resolveRole($role)->id]);
        $this->unsetRelation('roles');

        return $this;
    }

    /** @param array<int, RoleRecord|string> $roles */
    public function syncRoles(array $roles): static
    {
        $this->roles()->sync(collect($roles)->map(fn (RoleRecord|string $role) => $this->resolveRole($role)->id)->all());
        $this->unsetRelation('roles');

        return $this;
    }

    public function hasRole(string $name): bool
    {
        return $this->roles()->where('roles.name', $name)->exists();
    }

    /** @return Collection<int, string> */
    public function getRoleNames(): Collection
    {
        return $this->roles()->pluck('roles.name');
    }

    public function scopeRole(Builder $query, string $name): void
    {
        $query->whereHas('roles', fn (Builder $roles) => $roles->where('roles.name', $name));
    }

    private function resolveRole(RoleRecord|string $role): RoleRecord
    {
        return $role instanceof RoleRecord ? $role : RoleRecord::where('name', $role)->firstOrFail();
    }
}
