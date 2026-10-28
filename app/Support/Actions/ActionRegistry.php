<?php

namespace App\Support\Actions;

use InvalidArgumentException;

class ActionRegistry
{
    /** @var array<string, class-string<NamedAction>> */
    private array $actions = [];

    /** @param class-string<NamedAction> $action */
    public function register(string $action): void
    {
        $this->actions[$action::code()] = $action;
    }

    /** @return class-string<NamedAction> */
    public function find(string $code): string
    {
        return $this->actions[$code] ?? throw new InvalidArgumentException("Unknown action [{$code}].");
    }

    /** @return array<string, class-string<NamedAction>> */
    public function all(): array
    {
        return $this->actions;
    }

    /** @return array<string, class-string<NamedAction>> */
    public function aiExecutable(): array
    {
        return array_filter($this->actions, fn (string $action) => $action::aiExecutable());
    }
}
