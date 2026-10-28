<?php

namespace Modules\Administration\Console;

use App\Enums\DataSourceIndicator;
use App\Models\DataSource;
use App\Support\Connectors\IndicatorRule;
use Illuminate\Console\Command;

class RefreshConnectorFreshness extends Command
{
    protected $signature = 'connectors:refresh-freshness';

    protected $description = 'Flag data sources whose last synchronisation is older than the freshness threshold';

    public function handle(): int
    {
        $limit = now()->subHours(IndicatorRule::staleAfterHours());

        foreach (DataSource::all() as $source) {
            $isStale = $source->last_synced_at === null || $source->last_synced_at->lt($limit);

            if ($isStale === $source->is_stale) {
                continue;
            }

            $source->is_stale = $isStale;

            if ($isStale && $source->indicator_status === DataSourceIndicator::Green) {
                $source->indicator_status = DataSourceIndicator::Orange;
            }

            $source->save();
        }

        return self::SUCCESS;
    }
}
