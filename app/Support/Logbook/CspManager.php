<?php

namespace App\Support\Logbook;

use App\Models\User;
use App\Support\Authorization\Role;

final class CspManager
{
    public function name(): ?string
    {
        $configured = trim((string) config('surveillance.watch.csp_manager'));

        if ($configured !== '') {
            return $configured;
        }

        return User::query()->role(Role::Director->value)->where('is_active', true)->orderBy('id')->first()?->fullName();
    }
}
