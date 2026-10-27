<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role as RoleModel;

class RoleController extends Controller
{
    public const AUDIT_LOG = 'administration';

    public function index(): View
    {
        $order = collect(Role::cases())->pluck('value')->flip();

        return view('administration::roles.index', [
            'roles' => RoleModel::withCount('users')->with('permissions:id,name')->get()
                ->sortBy(fn (RoleModel $role) => Role::isSystem($role->name) ? sprintf('0%02d', $order[$role->name]) : '1'.$role->name)
                ->values(),
            'permissions' => Permission::cases(),
        ]);
    }

    public function create(): View
    {
        return view('administration::roles.create', ['role' => new RoleModel, 'granted' => [], ...$this->formData()]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = RoleModel::create(['name' => $request->validated('name'), 'description' => $request->validated('description'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        $this->audit($role, 'created', ['added' => $role->permissions->pluck('name')->sort()->values()->all()]);

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.created'));
    }

    public function edit(RoleModel $role): View
    {
        return view('administration::roles.edit', [
            'role' => $role,
            'granted' => $role->permissions->pluck('name')->all(),
            'isSystem' => Role::isSystem($role->name),
            'isLocked' => $role->name === Role::Admin->value,
            ...$this->formData(),
        ]);
    }

    public function update(RoleRequest $request, RoleModel $role): RedirectResponse
    {
        if (! Role::isSystem($role->name)) {
            if ($role->name !== $request->validated('name')) {
                $this->audit($role, 'updated', ['old' => $role->name, 'new' => $request->validated('name')]);
            }

            $role->update(['name' => $request->validated('name'), 'description' => $request->validated('description')]);
        }

        if ($role->name !== Role::Admin->value) {
            $this->syncPermissions($role, $request->validated('permissions', []));
        }

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.updated'));
    }

    public function destroy(RoleModel $role): RedirectResponse
    {
        $error = match (true) {
            Role::isSystem($role->name) => 'cannot_delete_system',
            $role->users()->exists() => 'cannot_delete_in_use',
            default => null,
        };

        if ($error !== null) {
            return to_route('administration.roles.index')->with('error', __("administration::administration.roles.{$error}"));
        }

        $this->audit($role, 'deleted', ['removed' => $role->permissions->pluck('name')->sort()->values()->all()]);
        $role->delete();

        return to_route('administration.roles.index')->with('status', __('administration::administration.roles.deleted'));
    }

    /** @return array{groups: Collection<string, Collection<int, Permission>>} */
    private function formData(): array
    {
        return ['groups' => collect(Permission::cases())->groupBy(fn (Permission $permission) => Str::before($permission->value, '.'))];
    }

    /** @param array<int, string> $names */
    private function syncPermissions(RoleModel $role, array $names): void
    {
        $before = $role->permissions->pluck('name');
        $role->syncPermissions($names);
        $after = collect($names);

        $added = $after->diff($before)->sort()->values()->all();
        $removed = $before->diff($after)->sort()->values()->all();

        if ($added !== [] || $removed !== []) {
            $this->audit($role, 'permissions_changed', ['added' => $added, 'removed' => $removed]);
        }
    }

    /** @param array<string, mixed> $properties */
    private function audit(RoleModel $role, string $event, array $properties): void
    {
        activity(self::AUDIT_LOG)->performedOn($role)->event($event)->withProperties($properties)->log($event);
    }
}
