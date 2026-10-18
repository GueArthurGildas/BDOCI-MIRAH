<?php

namespace App\Support\Presentation;

use App\Models\Vessel;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;

final class VesselLink
{
    public static function url(Vessel $vessel): ?string
    {
        if (! Route::has('vessels.show') || ! auth()->user()?->can(Permission::VesselsView->value)) {
            return null;
        }

        return route('vessels.show', $vessel);
    }
}
