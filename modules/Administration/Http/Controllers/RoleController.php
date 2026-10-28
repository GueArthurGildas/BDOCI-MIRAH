<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PermissionGrant;
use App\Models\RoleRecord;
use App\Models\Zone;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionResolver;
use App\Support\Authorization\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\RoleRequest;

class RoleController extends Controller
{
    public const AUDIT_LOG = 'administration';

    public function __construct(
        private readonly GrantService $grants,
        private readonly PermissionResolver $resolver,
    ) {}

    public function index(): View
    {
        $order = collect(Role::cases())->pluck('value')->flip();
        $roles = RoleRecord::withCount('users')->with('parent')->get()
            ->sortBy(fn (RoleRecord $role) => Role::isSystem($role->name) ? sprintf('0%02d', $order[$role->name]) : '1'.$role->name)
            ->values();

        return view('administration::roles.index', [
            'roles' => $roles,
            'permissions' => Permission::cases(),
            'effective' => $roles->mapWithKeys(fn (RoleRecord $role) => [$role->id => $this->resolver->actionsForRole($role)]),
        ]);
    }

    public function create(): View
    {
        return view('administration::roles.create', [
            'role' => new RoleRecord,
            'granted' => [],
            'inherited' => [],
            'parents' => $this->parentOptions(),
            ...$this->formData(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = RoleRecord::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'parent_id' => $request->validated('parent_id'),
        ]);
        $changes = $this->grants->syncRole($role, $request->validated('permissions', []));

        $this->audit($role, 'created', ['added' => $changes['added']]);

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.created'));
    }

    public function edit(RoleRecord $role): View
    {
        return view('administration::roles.edit', [
            'role' => $role,
            'granted' => $this->grants->directCodesOfRole($role),
            'inherited' => $this->resolver->inheritedActionsForRole($role),
            'parents' => $this->parentOptions($role),
            'isSystem' => Role::isSystem($role->name),
            'isLocked' => $role->name === Role::Admin->value,
            ...$this->grantPanel($role),
            ...$this->formData(),
        ]);
    }

    public function update(RoleRequest $request, RoleRecord $role): RedirectResponse
    {
        if (! Role::isSystem($role->name)) {
            if ($role->name !== $request->validated('name')) {
                $this->audit($role, 'updated', ['old' => $role->name, 'new' => $request->validated('name')]);
            }

            $role->update([
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'parent_id' => $request->validated('parent_id'),
            ]);
        }

        if ($role->name !== Role::Admin->value) {
            $changes = $this->grants->syncRole($role, $request->validated('permissions', []));

            if ($changes['added'] !== [] || $changes['removed'] !== []) {
                $this->audit($role, 'permissions_changed', $changes);
            }
        }

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.updated'));
    }

    public function destroy(RoleRecord $role): RedirectResponse
    {
        $error = match (true) {
            Role::isSystem($role->name) => 'cannot_delete_system',
            $role->users()->exists() => 'cannot_delete_in_use',
            $role->children()->exists() => 'cannot_delete_has_children',
            default => null,
        };

        if ($error !== null) {
            return to_route('administration.roles.index')->with('error', __("administration::administration.roles.{$error}"));
        }

        $this->audit($role, 'deleted', ['removed' => $this->grants->directCodesOfRole($role)]);
        $role->delete();

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.deleted'));
    }

    /** @return array<string, string> */
    private function parentOptions(?RoleRecord $role = null): array
    {
        $excluded = $role ? $role->selfAndDescendantIds() : [];

        return RoleRecord::whereNotIn('id', $excluded)->orderBy('name')->get()
            ->mapWithKeys(fn (RoleRecord $candidate) => [$candidate->id => Role::labelFor($candidate->name)])
            ->all();
    }

    /** @return array{scopedGrants: Collection<int, PermissionGrant>, actionOptions: array<string, string>, zoneOptions: array<int, string>} */
    private function grantPanel(RoleRecord $holder): array
    {
        return [
            'scopedGrants' => $this->grants->scopedGrants($holder),
            'actionOptions' => collect(Permission::cases())->mapWithKeys(fn (Permission $permission) => [$permission->value => __('administration::administration.permissions.'.$permission->value)])->all(),
            'zoneOptions' => Zone::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    /** @return array{groups: Collection<string, Collection<int, Permission>>} */
    private function formData(): array
    {
        return ['groups' => collect(Permission::cases())->groupBy(fn (Permission $permission) => Str::before($permission->value, '.'))];
    }

    /** @param array<string, mixed> $properties */
    private function audit(RoleRecord $role, string $event, array $properties): void
    {
        activity(self::AUDIT_LOG)->performedOn($role)->event($event)->withProperties($properties)->log($event);
    }
}
