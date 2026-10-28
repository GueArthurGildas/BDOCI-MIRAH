<?php

namespace Modules\Watch\Actions;

use App\Enums\WatchOrigin;
use App\Models\WatchEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Watch\WatchList;

class AddVesselToDirectorList extends Action
{
    public function __construct(private readonly WatchList $watchList) {}

    public function code(): string
    {
        return 'watch.add_to_director_list';
    }

    public function permission(): Permission
    {
        return Permission::WatchManage;
    }

    public function handle(ActionContext $context, array $input): WatchEntry
    {
        $entry = $this->watchList->open($input['vessel_id'], WatchOrigin::DirectorListManual, $context->actor);

        $context->publish(
            vessel: $entry->vessel_id,
            module: 'watch',
            type: 'watch.director_list_added',
            subject: $entry,
            summaryKey: 'watch::events.director_list_added',
        );

        return $entry;
    }
}
