<?php

namespace Modules\Reports\Console;

use App\Models\ScheduledReport;
use App\Support\Actions\ActionRunner;
use Illuminate\Console\Command;
use Modules\Reports\Actions\GenerateScheduledReport;
use Modules\Reports\Support\ReportsActor;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';

    protected $description = 'Produce the scheduled reports that are due and notify their recipients';

    public function handle(ActionRunner $actions, ReportsActor $system): int
    {
        $actor = $system->resolve();
        $produced = 0;

        ScheduledReport::where('is_active', true)->orderBy('id')->pluck('id')->each(function (int $id) use ($actions, $actor, &$produced) {
            $produced += $actions->run(GenerateScheduledReport::class, $actor, ['scheduled_report_id' => $id]) ? 1 : 0;
        });

        $this->info("{$produced} report(s) produced.");

        return self::SUCCESS;
    }
}
