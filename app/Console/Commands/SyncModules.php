<?php

namespace App\Console\Commands;

use App\Support\Modules\ModuleRegistry;
use Illuminate\Console\Command;

class SyncModules extends Command
{
    protected $signature = 'modules:sync';

    protected $description = 'Record the installed modules in the module registry';

    public function handle(ModuleRegistry $modules): int
    {
        $this->info("{$modules->sync()} modules registered.");

        return self::SUCCESS;
    }
}
