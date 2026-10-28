<?php

namespace Modules\Communications\Actions;

use App\Enums\DispatchMode;
use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Models\CommunicationChannel;
use App\Models\DispatchRule;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Collection;
use Modules\Communications\Support\MessageDispatcher;
use Modules\Communications\Support\MessageRenderer;
use Modules\Communications\Support\StopConditions;
use Modules\Communications\Support\TriggerEvents;

class DispatchRuleMessages extends Action
{
    public function __construct(
        private readonly MessageDispatcher $dispatcher,
        private readonly MessageRenderer $renderer,
        private readonly TriggerEvents $triggers,
        private readonly StopConditions $stopConditions,
    ) {}

    public function code(): string
    {
        return 'communications.dispatch_rule';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsManageDispatch;
    }

    public function handle(ActionContext $context, array $input): DispatchRule
    {
        $rule = DispatchRule::with('template')->lockForUpdate()->findOrFail($input['dispatch_rule_id']);
        $dispatched = new Collection;

        $recipient = $rule->conditions['recipient'] ?? null;

        if ($rule->is_active && $rule->template->is_active && $recipient) {
            $channel = CommunicationChannel::forCode($rule->template->channel);
            $automatic = $rule->mode === DispatchMode::Automatic && $channel->is_auto_dispatch_enabled;
            $remaining = $this->remainingToday($rule);

            foreach ($this->triggers->vessels($rule->trigger_event) as $vessel) {
                if ($remaining <= 0) {
                    break;
                }

                if (! $this->isDue($rule, $vessel, $recipient, $automatic)) {
                    continue;
                }

                $dispatched->push($this->dispatchTo($context, $rule, $vessel, $recipient, $automatic));
                $remaining--;
            }
        }

        return $rule->setRelation('dispatched', $dispatched);
    }

    private function remainingToday(DispatchRule $rule): int
    {
        $cap = $rule->daily_cap ?? config('communications.default_daily_cap');

        return $cap - $rule->messages()->where('created_at', '>=', today())->count();
    }

    private function isDue(DispatchRule $rule, Vessel $vessel, string $recipient, bool $automatic): bool
    {
        if ($this->stopConditions->isMet($rule, $vessel)) {
            return false;
        }

        if ($automatic && ! $this->dispatcher->canDeliver($rule->template->channel, $recipient)) {
            return false;
        }

        $forVessel = fn () => $rule->messages()->where('vessel_id', $vessel->id);

        if ($forVessel()->where('status', MessageStatus::Prepared->value)->exists()) {
            return false;
        }

        return $rule->frequency_minutes === null
            || ! $forVessel()->where('created_at', '>', now()->subMinutes($rule->frequency_minutes))->exists();
    }

    private function dispatchTo(ActionContext $context, DispatchRule $rule, Vessel $vessel, string $recipient, bool $automatic): SentMessage
    {
        $rendered = $this->renderer->render($rule->template, $vessel);

        $message = $this->dispatcher->prepare($rule->template->channel, [
            'message_template_id' => $rule->template->id,
            'dispatch_rule_id' => $rule->id,
            'vessel_id' => $vessel->id,
            'recipient' => $recipient,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'triggered_by' => MessageTrigger::Rule->value,
        ]);

        if ($automatic) {
            $this->dispatcher->deliver($message);
        }

        $context->publish(
            vessel: $vessel,
            module: 'communications',
            type: "message.{$message->status->value}",
            subject: $message,
            summaryKey: "communications::events.message_{$message->status->value}",
            params: ['template' => $rule->template->code, 'recipient' => $recipient],
        );

        return $message;
    }
}
