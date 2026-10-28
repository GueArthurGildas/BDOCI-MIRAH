<?php

namespace App\Support\Actions;

use App\Support\Authorization\Permission;

interface NamedAction
{
    public static function code(): string;

    public static function permission(): Permission;

    public static function aiExecutable(): bool;

    /** @param array<string, mixed> $input */
    public function handle(ActionContext $context, array $input): mixed;
}
