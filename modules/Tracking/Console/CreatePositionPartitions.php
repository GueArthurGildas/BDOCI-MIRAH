<?php

namespace Modules\Tracking\Console;

use App\Support\Vms\PositionPartitions;
use Illuminate\Console\Command;

class CreatePositionPartitions extends Command
{
    protected $signature = 'tracking:create-position-partitions {--months=3 : Number of months to prepare ahead}';

    protected $description = 'Create the monthly partitions of the positions table';

    public function handle(PositionPartitions $partitions): int
    {
        $this->info($partitions->ensureAhead((int) $this->option('months')).' partition(s) created.');

        return self::SUCCESS;
    }
}
