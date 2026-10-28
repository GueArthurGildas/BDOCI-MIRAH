<?php

namespace App\Support\Authorization;

use App\Models\PermissionGrant;
use App\Models\PermissionGroup;
use App\Models\RoleRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class GrantService
{
    public function __construct(
        private readonly ActionCatalog $catalog,
        private readonly PermissionResolver $resolver,
    ) {}

    /**
     * @param  array<int, string>  $codes
     * @return array{added: array<int, string>, removed: array<int, string>}
     */
    public function syncRole(RoleRecord $role, array $codes): array
    {
        return $this->sync($role, 'role_id', $codes);
    }

    /**
     * @param  array<int, string>  $codes
     * @return array{added: array<int, string>, removed: array<int, string>}
     */
    public function syncGroup(PermissionGroup $group, array $codes): array
    {
        return $this->sync($group, 'permission_group_id', $codes);
    }

    /** @return array<int, string> permanent, unscoped grants held directly by the role */
    public function directCodesOfRole(RoleRecord $role): array
    {
        return $this->directCodes('role_id', $role->id);
    }

    /** @return array<int, string> */
    public function directCodesOfGroup(PermissionGroup $group): array
    {
        return $this->directCodes('permission_group_id', $group->id);
    }

    public function addScoped(RoleRecord|PermissionGroup $holder, string $code, ?int $zoneId, ?string $validFrom, ?string $validTo): PermissionGrant
    {
        $grant = PermissionGrant::firstOrCreate([
            $this->columnOf($holder) => $holder->getKey(),
            'action_id' => $this->catalog->idFor($code),
            'zone_id' => $zoneId,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
        ]);

        $this->resolver->flush();

        return $grant;
    }

    public function remove(PermissionGrant $grant): void
    {
        $grant->delete();
        $this->resolver->flush();
    }

    /** @return Collection<int, PermissionGrant> grants of the holder that are limited to a zone or a period */
    public function scopedGrants(RoleRecord|PermissionGroup $holder): Collection
    {
        return PermissionGrant::query()
            ->where($this->columnOf($holder), $holder->getKey())
            ->where(fn ($query) => $query->whereNotNull('zone_id')->orWhereNotNull('valid_from')->orWhereNotNull('valid_to'))
            ->with(['action:id,code', 'zone:id,name'])
            ->orderBy('id')
            ->get();
    }

    private function columnOf(RoleRecord|PermissionGroup $holder): string
    {
        return $holder instanceof RoleRecord ? 'role_id' : 'permission_group_id';
    }

    /**
     * @param  array<int, string>  $codes
     * @return array{added: array<int, string>, removed: array<int, string>}
     */
    private function sync(Model $holder, string $column, array $codes): array
    {
        $before = $this->directCodes($column, $holder->getKey());
        $codes = array_values(array_unique($codes));
        $added = array_values(array_diff($codes, $before));
        $removed = array_values(array_diff($before, $codes));

        foreach ($added as $code) {
            PermissionGrant::create([$column => $holder->getKey(), 'action_id' => $this->catalog->idFor($code)]);
        }

        if ($removed !== []) {
            $this->permanentGrants($column, $holder->getKey())->whereHas('action', fn ($action) => $action->whereIn('code', $removed))->delete();
        }

        $this->resolver->flush();

        sort($added);
        sort($removed);

        return ['added' => $added, 'removed' => $removed];
    }

    /** @return array<int, string> */
    private function directCodes(string $column, int|string $holderId): array
    {
        return $this->permanentGrants($column, $holderId)->with('action:id,code')->get()->pluck('action.code')->sort()->values()->all();
    }

    private function permanentGrants(string $column, int|string $holderId)
    {
        return PermissionGrant::query()->where($column, $holderId)->whereNull('zone_id')->whereNull('valid_from')->whereNull('valid_to');
    }
}
