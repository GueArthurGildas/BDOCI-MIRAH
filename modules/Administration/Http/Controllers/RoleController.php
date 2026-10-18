<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __invoke(): View
    {
        return view('administration::roles', [
            'roles' => Role::cases(),
            'permissions' => Permission::cases(),
        ]);
    }
}
