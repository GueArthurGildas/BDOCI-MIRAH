<?php

namespace Modules\Administration\Console;

use App\Enums\DataSourceMode;
use App\Models\DataSource;
use App\Models\User;
use App\Support\Actions\ActionRunner;
use App\Support\Connectors\ConnectorRegistry;
use Illuminate\Console\Command;
use Modules\Administration\Actions\SynchroniseConnector;

class SynchroniseConnectors extends Command
{
    protected $signature = 'connectors:sync {code? : Code of one data source}';

    protected $description = 'Synchronise the data sources that have a connector and run in a scheduled mode';

    public function handle(ConnectorRegistry $registry, ActionRunner $actions): int
    {
        $actor = User::role(config('connectors.sync_actor_role'))->where('is_active', true)->orderBy('id')->first();

        if ($actor === null) {
            $this->components->error('No active administrator can run the synchronisation.');

            return self::FAILURE;
        }

        $sources = DataSource::query()
            ->whereIn('code', $registry->codes())
            ->when($this->argument('code'), fn ($query, string $code) => $query->where('code', $code))
            ->where('mode', '!=', DataSourceMode::Import->value)
            ->get();

        foreach ($sources as $source) {
            $sync = $actions->run(SynchroniseConnector::class, $actor, ['data_source_id' => $source->id]);
            $this->components->info("{$source->code}: {$sync->received_count} received, {$sync->rejected_count} rejected");
        }

        return self::SUCCESS;
    }
}
