<?php

namespace App\Support\Actions;

use App\Support\Authorization\Permission;
use App\Support\Settings\Settings;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ActionRunner
{
    public function __construct(private readonly ActionRegistry $registry) {}

    /** @param array<string, mixed> $input */
    public function run(string $code, ActionContext $context, array $input = []): mixed
    {
        $action = $this->registry->find($code);

        $this->authorize($action, $context);

        $result = DB::transaction(fn () => app($action)->handle($context, $input));

        $this->audit($action, $context, $input);

        return $result;
    }

    /** @param class-string<NamedAction> $action */
    private function authorize(string $action, ActionContext $context): void
    {
        if (! $context->isAi()) {
            throw_unless(
                $context->executor->can($action::permission()->value),
                AuthorizationException::class,
            );

            return;
        }

        $operator = $context->requestedBy;

        throw_if($operator === null, AuthorizationException::class, 'An AI action must be requested by an operator.');
        throw_unless($action::aiExecutable(), AuthorizationException::class, 'This action cannot be executed by the AI agent.');
        throw_unless(app(Settings::class)->aiActionEnabled($action::code()), AuthorizationException::class, 'This action is not enabled for the AI agent.');
        throw_unless($operator->can(Permission::AiTrigger->value), AuthorizationException::class, 'The operator may not trigger the AI agent.');
        throw_unless($operator->can($action::permission()->value), AuthorizationException::class, 'The operator lacks the permission required by this action.');
    }

    /**
     * @param  class-string<NamedAction>  $action
     * @param  array<string, mixed>  $input
     */
    private function audit(string $action, ActionContext $context, array $input): void
    {
        activity('actions')
            ->causedBy($context->executor)
            ->event($action::code())
            ->withProperties([
                'executed_by_ai' => $context->isAi(),
                'requested_by_id' => $context->requestedBy?->getKey(),
                'requested_by_name' => $context->requestedBy?->name,
                'input' => $input,
            ])
            ->log('action_executed');
    }
}
