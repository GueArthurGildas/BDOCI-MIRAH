<?php

namespace App\Support\Navigation;

use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Support\Facades\Route;

final class Landing
{
    public static function routeName(?User $user): string
    {
        $isDirector = $user?->hasRole(Role::Director->value) && $user->can(Permission::IndicatorsView->value);

        return $isDirector && Route::has('fleet.index') ? 'fleet.index' : 'dashboard';
    }
}
