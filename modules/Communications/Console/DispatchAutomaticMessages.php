<?php

namespace Modules\Communications\Console;

use App\Enums\MessageStatus;
use App\Models\DispatchRule;
use App\Models\SentMessage;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use Illuminate\Console\Command;
use Modules\Communications\Actions\DispatchRuleMessages;
use Modules\Communications\Notifications\PreparedMessagesAlert;
use Modules\Communications\Support\SystemActor;

class DispatchAutomaticMessages extends Command
{
    protected $signature = 'communications:dispatch';

    protected $description = 'Evaluate the active dispatch rules and send or prepare their messages';

    public function handle(ActionRunner $actions, SystemActor $system, Notifier $notifier): int
    {
        if (! config('communications.dispatch_enabled')) {
            return self::SUCCESS;
        }

        $actor = $system->resolve();
        $count = 0;

        DispatchRule::where('is_active', true)->orderBy('id')->pluck('id')->each(function (int $ruleId) use ($actions, $actor, &$count) {
            $count += $actions->run(DispatchRuleMessages::class, $actor, ['dispatch_rule_id' => $ruleId])->dispatched->count();
        });

        $this->info("{$count} message(s) dispatched or prepared.");

        $this->alertPreparedMessages($notifier);

        return self::SUCCESS;
    }

    private function alertPreparedMessages(Notifier $notifier): void
    {
        if (! config('communications.alerts.prepared_messages.enabled')) {
            return;
        }

        $prepared = SentMessage::where('status', MessageStatus::Prepared->value)->count();

        if ($prepared > 0) {
            $notifier->notifyPermissionHolders(Permission::CommunicationsSend, new PreparedMessagesAlert($prepared), config('communications.alerts.prepared_messages.cooldown_hours'));
        }
    }
}
