<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PermissionGrant;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Models\Zone;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\GroupRequest;

class GroupController extends Controller
{
    public const AUDIT_LOG = 'administration';

    public function __construct(private readonly GrantService $grants) {}

    public function index(): View
    {
        return view('administration::groups.index', [
            'groups' => PermissionGroup::withCount(['members', 'grants'])->orderByDesc('is_system')->orderBy('code')->get(),
        ]);
    }

    public function create(): View
    {
        return view('administration::groups.create', ['group' => new PermissionGroup, 'granted' => [], 'members' => [], ...$this->formData()]);
    }

    public function store(GroupRequest $request): RedirectResponse
    {
        $group = PermissionGroup::create(['code' => $this->uniqueCode($request->validated('label')), 'label' => $request->validated('label')]);

        $this->syncGrants($group, $request->validated('permissions', []));
        $this->syncMembers($group, $request->validated('members', []));
        $this->audit($group, 'created', ['label' => $group->label]);

        return to_route('administration.groups.index')->with('status', __('administration::groups.created'));
    }

    public function edit(PermissionGroup $group): View
    {
        return view('administration::groups.edit', [
            'group' => $group,
            'granted' => $this->grants->directCodesOfGroup($group),
            'members' => $group->members()->pluck('users.id')->all(),
            ...$this->grantPanel($group),
            ...$this->formData(),
        ]);
    }

    public function update(GroupRequest $request, PermissionGroup $group): RedirectResponse
    {
        if (! $group->is_system && $group->label !== $request->validated('label')) {
            $this->audit($group, 'updated', ['old' => $group->label, 'new' => $request->validated('label')]);
            $group->update(['label' => $request->validated('label')]);
        }

        $this->syncGrants($group, $request->validated('permissions', []));
        $this->syncMembers($group, $request->validated('members', []));

        return to_route('administration.groups.index')->with('status', __('administration::groups.updated'));
    }

    public function destroy(PermissionGroup $group): RedirectResponse
    {
        if ($group->is_system) {
            return to_route('administration.groups.index')->with('error', __('administration::groups.cannot_delete_system'));
        }

        $this->audit($group, 'deleted', ['removed' => $this->grants->directCodesOfGroup($group)]);
        $group->delete();

        return to_route('administration.groups.index')->with('status', __('administration::groups.deleted'));
    }

    /** @return array{scopedGrants: Collection<int, PermissionGrant>, actionOptions: array<string, string>, zoneOptions: array<int, string>} */
    private function grantPanel(PermissionGroup $holder): array
    {
        return [
            'scopedGrants' => $this->grants->scopedGrants($holder),
            'actionOptions' => collect(Permission::cases())->mapWithKeys(fn (Permission $permission) => [$permission->value => __('administration::administration.permissions.'.$permission->value)])->all(),
            'zoneOptions' => Zone::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    /** @return array{modules: Collection<string, Collection<int, Permission>>, users: Collection<int, User>} */
    private function formData(): array
    {
        return [
            'modules' => collect(Permission::cases())->groupBy(fn (Permission $permission) => Str::before($permission->value, '.')),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    /** @param array<int, string> $codes */
    private function syncGrants(PermissionGroup $group, array $codes): void
    {
        $changes = $this->grants->syncGroup($group, $codes);

        if ($changes['added'] !== [] || $changes['removed'] !== []) {
            $this->audit($group, 'permissions_changed', $changes);
        }
    }

    /** @param array<int, int|string> $userIds */
    private function syncMembers(PermissionGroup $group, array $userIds): void
    {
        $changes = $group->members()->sync($userIds);

        if ($changes['attached'] !== [] || $changes['detached'] !== []) {
            $this->audit($group, 'members_changed', [
                'added' => User::whereKey($changes['attached'])->pluck('name')->sort()->values()->all(),
                'removed' => User::whereKey($changes['detached'])->pluck('name')->sort()->values()->all(),
            ]);
        }
    }

    private function uniqueCode(string $label): string
    {
        $base = Str::slug($label, '_') ?: 'group';
        $code = $base;

        for ($suffix = 2; PermissionGroup::where('code', $code)->exists(); $suffix++) {
            $code = "{$base}_{$suffix}";
        }

        return $code;
    }

    /** @param array<string, mixed> $properties */
    private function audit(PermissionGroup $group, string $event, array $properties): void
    {
        activity(self::AUDIT_LOG)->performedOn($group)->event($event)->withProperties($properties)->log($event);
    }
}
