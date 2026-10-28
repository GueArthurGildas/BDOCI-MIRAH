<?php

namespace App\Support\Actions;

use App\Support\Authorization\Permission;

abstract class Action
{
    abstract public function code(): string;

    abstract public function permission(): Permission;

    /** @param array<string, mixed> $input */
    abstract public function handle(ActionContext $context, array $input): mixed;
}
