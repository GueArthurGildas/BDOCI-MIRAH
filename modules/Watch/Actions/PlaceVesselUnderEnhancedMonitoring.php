<?php

namespace Modules\Watch\Actions;

use App\Enums\WatchOrigin;
use App\Models\WatchEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Watch\WatchList;

class PlaceVesselUnderEnhancedMonitoring extends Action
{
    public function __construct(private readonly WatchList $watchList) {}

    public function code(): string
    {
        return 'watch.place_under_enhanced_monitoring';
    }

    public function permission(): Permission
    {
        return Permission::WatchCreate;
    }

    public function handle(ActionContext $context, array $input): WatchEntry
    {
        $origin = WatchOrigin::from($input['origin'] ?? WatchOrigin::EnhancedMonitoring->value);

        $entry = $this->watchList->open($input['vessel_id'], $origin, $context->actor);

        $context->publish(
            vessel: $entry->vessel_id,
            module: 'watch',
            type: 'watch.enhanced_monitoring_started',
            subject: $entry,
            summaryKey: 'watch::events.enhanced_monitoring_started.'.$origin->value,
        );

        return $entry;
    }
}
