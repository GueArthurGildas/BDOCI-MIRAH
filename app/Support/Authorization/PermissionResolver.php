<?php

namespace App\Support\Authorization;

use App\Models\PermissionGrant;
use App\Models\RoleRecord;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PermissionResolver
{
    /** @var array<int, array{unscoped: array<string, true>, scoped: array<string, array<int, true>>}> */
    private array $effective = [];

    /** @var array<int, ?int>|null */
    private ?array $parents = null;

    public function allows(User $user, string $action, ?Zone $zone = null): bool
    {
        $effective = $this->effectiveFor($user);

        return isset($effective['unscoped'][$action])
            || ($zone !== null && isset($effective['scoped'][$action][$zone->id]));
    }

    public function flush(): void
    {
        $this->effective = [];
        $this->parents = null;
    }

    /** @return array<int, string> actions held without zone restriction */
    public function actionsFor(User $user): array
    {
        return array_keys($this->effectiveFor($user)['unscoped']);
    }

    /** @return array<int, string> actions a role holds directly or through its ancestors */
    public function actionsForRole(RoleRecord $role): array
    {
        return array_keys($this->grantedTo(roleIds: $this->withAncestors([$role->id]), groupIds: [])['unscoped']);
    }

    /** @return array<int, string> actions inherited from the ancestors of a role, excluding its own grants */
    public function inheritedActionsForRole(RoleRecord $role): array
    {
        $ancestors = array_diff($this->withAncestors([$role->id]), [$role->id]);

        return array_keys($this->grantedTo(roleIds: array_values($ancestors), groupIds: [])['unscoped']);
    }

    /** @return Collection<int, User> active users holding the action without zone restriction */
    public function holders(string $action): Collection
    {
        $grantedRoles = $this->roleIdsWithDirectGrant($action);
        $roleIds = collect($this->parentMap())
            ->keys()
            ->filter(fn (int $roleId) => array_intersect($this->withAncestors([$roleId]), $grantedRoles) !== [])
            ->values()
            ->all();
        $groupIds = PermissionGrant::query()->whereNotNull('permission_group_id')->whereRelation('action', 'code', $action)
            ->where(fn ($query) => $this->validToday($query))->whereNull('zone_id')->pluck('permission_group_id')->all();

        $userIds = DB::table('role_user')->whereIn('role_id', $roleIds)->pluck('user_id')
            ->merge(DB::table('permission_group_user')->whereIn('permission_group_id', $groupIds)->pluck('user_id'))
            ->unique()
            ->all();

        return User::whereKey($userIds)->where('is_active', true)->get();
    }

    /** @return array{unscoped: array<string, true>, scoped: array<string, array<int, true>>} */
    private function effectiveFor(User $user): array
    {
        return $this->effective[$user->id] ??= $this->grantedTo(
            roleIds: $this->withAncestors(
                DB::table('role_user')->where('user_id', $user->id)->pluck('role_id')->all(),
            ),
            groupIds: DB::table('permission_group_user')->where('user_id', $user->id)->pluck('permission_group_id')->all(),
        );
    }

    /**
     * @param  array<int, int>  $roleIds
     * @param  array<int, int>  $groupIds
     * @return array{unscoped: array<string, true>, scoped: array<string, array<int, true>>}
     */
    private function grantedTo(array $roleIds, array $groupIds): array
    {
        $result = ['unscoped' => [], 'scoped' => []];

        if ($roleIds === [] && $groupIds === []) {
            return $result;
        }

        PermissionGrant::query()
            ->join('action_catalog', 'action_catalog.id', '=', 'permission_grants.action_id')
            ->where(fn ($query) => $query->whereIn('role_id', $roleIds)->orWhereIn('permission_group_id', $groupIds))
            ->where(fn ($query) => $this->validToday($query))
            ->get(['action_catalog.code', 'permission_grants.zone_id'])
            ->each(function ($grant) use (&$result) {
                if ($grant->zone_id === null) {
                    $result['unscoped'][$grant->code] = true;
                } else {
                    $result['scoped'][$grant->code][$grant->zone_id] = true;
                }
            });

        return $result;
    }

    /** @return array<int, int> */
    private function roleIdsWithDirectGrant(string $action): array
    {
        return PermissionGrant::query()->whereNotNull('role_id')->whereNull('zone_id')->whereRelation('action', 'code', $action)
            ->where(fn ($query) => $this->validToday($query))->pluck('role_id')->all();
    }

    private function validToday($query): void
    {
        $query->where(fn ($period) => $period->whereNull('permission_grants.valid_from')->orWhere('permission_grants.valid_from', '<=', today()))
            ->where(fn ($period) => $period->whereNull('permission_grants.valid_to')->orWhere('permission_grants.valid_to', '>=', today()));
    }

    /**
     * @param  array<int, int>  $roleIds
     * @return array<int, int>
     */
    private function withAncestors(array $roleIds): array
    {
        $all = [];
        $parents = $this->parentMap();

        foreach ($roleIds as $roleId) {
            for ($current = $roleId; $current !== null && ! in_array($current, $all, true); $current = $parents[$current] ?? null) {
                $all[] = $current;
            }
        }

        return array_values(array_unique($all));
    }

    /** @return array<int, ?int> */
    private function parentMap(): array
    {
        return $this->parents ??= RoleRecord::query()->pluck('parent_id', 'id')->all();
    }
}
