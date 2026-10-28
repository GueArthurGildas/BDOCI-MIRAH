<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PermissionGrant;
use App\Models\PermissionGroup;
use App\Models\RoleRecord;
use App\Support\Authorization\GrantService;
use App\Support\Authorization\Role;
use Illuminate\Http\RedirectResponse;
use Modules\Administration\Http\Requests\GrantRequest;

class GrantController extends Controller
{
    public const AUDIT_LOG = 'administration';

    public function __construct(private readonly GrantService $grants) {}

    public function storeForRole(GrantRequest $request, RoleRecord $role): RedirectResponse
    {
        abort_if($role->name === Role::Admin->value, 403);

        return $this->store($request, $role, route('administration.roles.edit', $role));
    }

    public function storeForGroup(GrantRequest $request, PermissionGroup $group): RedirectResponse
    {
        return $this->store($request, $group, route('administration.groups.edit', $group));
    }

    public function destroy(PermissionGrant $grant): RedirectResponse
    {
        $holder = $grant->role_id ? RoleRecord::findOrFail($grant->role_id) : PermissionGroup::findOrFail($grant->permission_group_id);
        $grant->load(['action', 'zone']);

        $this->audit($holder, 'grant_removed', $this->properties($grant));
        $this->grants->remove($grant);

        return back()->with('status', __('administration::grants.removed'));
    }

    private function store(GrantRequest $request, RoleRecord|PermissionGroup $holder, string $redirectTo): RedirectResponse
    {
        $grant = $this->grants->addScoped(
            $holder,
            $request->validated('action'),
            $request->validated('zone_id') ?: null,
            $request->validated('valid_from') ?: null,
            $request->validated('valid_to') ?: null,
        );
        $grant->load(['action', 'zone']);

        $this->audit($holder, 'grant_added', $this->properties($grant));

        return redirect($redirectTo)->with('status', __('administration::grants.added'));
    }

    /** @return array<string, mixed> */
    private function properties(PermissionGrant $grant): array
    {
        return [
            'action' => $grant->action->code,
            'zone' => $grant->zone?->name,
            'valid_from' => $grant->valid_from?->toDateString(),
            'valid_to' => $grant->valid_to?->toDateString(),
        ];
    }

    /** @param array<string, mixed> $properties */
    private function audit(RoleRecord|PermissionGroup $holder, string $event, array $properties): void
    {
        activity(self::AUDIT_LOG)->performedOn($holder)->event($event)->withProperties($properties)->log($event);
    }
}
