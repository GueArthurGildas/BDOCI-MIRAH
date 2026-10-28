<?php

namespace App\Console\Commands;

use App\Support\Events\VesselStateBuilder;
use Illuminate\Console\Command;

class RebuildVesselStates extends Command
{
    protected $signature = 'vessels:rebuild-state';

    protected $description = 'Rebuild the current state of every vessel from the source tables';

    public function handle(VesselStateBuilder $states): int
    {
        $this->info("{$states->rebuildAll()} vessel states rebuilt.");

        return self::SUCCESS;
    }
}
