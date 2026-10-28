<?php

namespace App\Console\Commands;

use App\Support\Authorization\ActionCatalog;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Record every permission declared in code in the action catalog';

    public function handle(ActionCatalog $catalog): int
    {
        $this->info("{$catalog->sync()} actions in the catalog.");

        return self::SUCCESS;
    }
}
