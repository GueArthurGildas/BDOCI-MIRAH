<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
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
        $user = User::create($request->safe()->except('role'));
        $this->assignRole($user, $request->validated('role'));

        return to_route('administration.users.index')->with('status', __('administration::administration.users.created'));
    }

    public function edit(User $user): View
    {
        return view('administration::users.edit', ['user' => $user, 'teams' => $this->teams()]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update(array_filter($request->safe()->except('role'), fn ($value, string $field) => $field !== 'password' || filled($value), ARRAY_FILTER_USE_BOTH));
        $this->assignRole($user, $request->validated('role'));

        return to_route('administration.users.index')->with('status', __('administration::administration.users.updated'));
    }

    private function assignRole(User $user, string $role): void
    {
        $previousRoles = $user->getRoleNames()->all();

        if ($previousRoles === [$role]) {
            return;
        }

        $user->syncRoles([$role]);

        activity(self::AUDIT_LOG)
            ->performedOn($user)
            ->event('role_changed')
            ->withProperties(['old' => $previousRoles, 'new' => [$role]])
            ->log('role_changed');
    }

    private function teams(): array
    {
        return Team::orderBy('name')->pluck('name', 'id')->all();
    }
}
