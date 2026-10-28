<?php

namespace App\Support\Actions;

use App\Enums\UserKind;
use App\Models\User;

final readonly class ActionContext
{
    public function __construct(
        public User $executor,
        public ?User $requestedBy = null,
    ) {}

    public static function human(User $user): self
    {
        return new self($user);
    }

    public static function aiOnBehalfOf(User $agent, User $operator): self
    {
        return new self($agent, $operator);
    }

    public function isAi(): bool
    {
        return $this->executor->kind === UserKind::AiAgent;
    }
}
