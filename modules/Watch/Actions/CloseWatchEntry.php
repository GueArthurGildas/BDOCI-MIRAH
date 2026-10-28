<?php

namespace Modules\Watch\Actions;

use App\Enums\WatchOrigin;
use App\Models\CheckCounter;
use App\Models\WatchEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Watch\WatchList;

class CloseWatchEntry extends Action
{
    public function __construct(private readonly WatchList $watchList) {}

    public function code(): string
    {
        return 'watch.close_entry';
    }

    public function permission(): Permission
    {
        return Permission::WatchManage;
    }

    public function handle(ActionContext $context, array $input): WatchEntry
    {
        $entry = WatchEntry::findOrFail($input['watch_entry_id']);

        $this->watchList->close($entry);

        if ($entry->origin === WatchOrigin::MoreThan3Checks) {
            CheckCounter::where('vessel_id', $entry->vessel_id)->update(['checks_count' => 0, 'is_alert_raised' => false]);
        }

        $context->publish(
            vessel: $entry->vessel_id,
            module: 'watch',
            type: 'watch.closed',
            subject: $entry,
            summaryKey: 'watch::events.closed',
        );

        return $entry;
    }
}
