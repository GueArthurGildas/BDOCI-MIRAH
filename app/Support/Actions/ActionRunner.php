<?php

namespace App\Support\Actions;

use App\Models\User;
use App\Models\VesselEvent;
use App\Support\Events\VesselStateBuilder;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class ActionRunner
{
    public const AUDIT_LOG = 'actions';

    public function __construct(
        private readonly ModuleRegistry $modules,
        private readonly VesselStateBuilder $states,
    ) {}

    /**
     * @param  class-string<Action>|Action  $action
     * @param  array<string, mixed>  $input
     */
    public function run(string|Action $action, User $actor, array $input = [], ?User $requestedBy = null): mixed
    {
        $action = is_string($action) ? app($action) : $action;

        if (! $actor->can($action->permission()->value)) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($action, $actor, $input, $requestedBy) {
            $context = new ActionContext($actor, $requestedBy);
            $result = $action->handle($context, $input);

            $this->persistEvents($context, $actor);

            foreach ($context->touchedVesselIds() as $vesselId) {
                $this->states->refresh($vesselId);
            }

            $this->audit($action, $context, $result, $input);

            return $result;
        });
    }

    private function persistEvents(ActionContext $context, User $actor): void
    {
        foreach ($context->events() as $event) {
            VesselEvent::create([...Arr::except($event, 'module'), 'module_id' => $this->modules->idFor($event['module']), 'user_id' => $actor->id]);
        }
    }

    /** @param array<string, mixed> $input */
    private function audit(Action $action, ActionContext $context, mixed $result, array $input): void
    {
        $entry = activity(self::AUDIT_LOG)->causedBy($context->actor)->event($action->code())->withProperties([
            'input' => $input,
            'requested_by' => $context->requestedBy?->id,
            'ip' => request()->ip(),
        ]);

        if ($result instanceof Model) {
            $entry->performedOn($result);
        }

        $entry->log($action->code());
    }
}
