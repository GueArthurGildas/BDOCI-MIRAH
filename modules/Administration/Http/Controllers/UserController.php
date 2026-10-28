<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Support\Authorization\PermissionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\UserRequest;

class UserController extends Controller
{
    private const PER_PAGE = 20;

    public const AUDIT_LOG = 'administration';

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->with(['team', 'roles'])
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('email', "%{$search}%")))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('administration::users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        return view('administration::users.create', ['user' => new User(['is_active' => true]), 'teams' => $this->teams()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->except(['role', 'groups']));
        $this->assignRoles($user, $request->validated('role'), $request->validated('groups', []));

        return to_route('administration.users.index')->with('status', __('administration::administration.users.created'));
    }

    public function edit(User $user): View
    {
        return view('administration::users.edit', ['user' => $user, 'teams' => $this->teams()]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update(array_filter($request->safe()->except(['role', 'groups']), fn ($value, string $field) => $field !== 'password' || filled($value), ARRAY_FILTER_USE_BOTH));
        $this->assignRoles($user, $request->validated('role'), $request->validated('groups', []));

        return to_route('administration.users.index')->with('status', __('administration::administration.users.updated'));
    }

    /** @param array<int, string> $groups */
    private function assignRoles(User $user, string $role, array $groups): void
    {
        $roles = [$role, ...array_map(fn (string $group) => PermissionGroup::from($group)->roleName(), $groups)];
        $previousRoles = $user->getRoleNames()->all();

        if (collect($previousRoles)->sort()->values()->all() === collect($roles)->sort()->values()->all()) {
            return;
        }

        $user->syncRoles($roles);

        activity(self::AUDIT_LOG)
            ->performedOn($user)
            ->event('role_changed')
            ->withProperties(['old' => $previousRoles, 'new' => $roles])
            ->log('role_changed');
    }

    private function teams(): array
    {
        return Team::orderBy('name')->pluck('name', 'id')->all();
    }
}
