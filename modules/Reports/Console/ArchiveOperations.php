<?php

namespace Modules\Reports\Console;

use App\Support\Actions\ActionRunner;
use Illuminate\Console\Command;
use Modules\Reports\Actions\ArchiveOperation;
use Modules\Reports\Support\ArchivableOperations;
use Modules\Reports\Support\ReportsActor;

class ArchiveOperations extends Command
{
    protected $signature = 'reports:archive-operations';

    protected $description = 'Archive the closed operations older than the configured number of years';

    public function handle(ActionRunner $actions, ReportsActor $system, ArchivableOperations $archivable): int
    {
        $actor = $system->resolve();
        $ids = $archivable->query()->orderBy('id')->pluck('id');

        $ids->each(fn (int $id) => $actions->run(ArchiveOperation::class, $actor, ['operation_id' => $id]));

        $this->info("{$ids->count()} operation(s) archived.");

        return self::SUCCESS;
    }
}
