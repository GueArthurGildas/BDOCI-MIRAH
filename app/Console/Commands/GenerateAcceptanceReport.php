<?php

namespace App\Console\Commands;

use App\Support\Documentation\AcceptanceReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateAcceptanceReport extends Command
{
    protected $signature = 'docs:acceptance {--check : Fail when the report is not up to date or cites unknown identifiers}';

    protected $description = 'Report which tests cover each use case and business rule of the specification';

    public function handle(AcceptanceReport $report): int
    {
        $path = base_path(AcceptanceReport::OUTPUT);
        $content = $report->render();
        $unknown = $report->unknownIds();

        foreach ($unknown as $id) {
            $this->error("A test cites {$id}, which is not in ".AcceptanceReport::CATALOG.'.');
        }

        if ($this->option('check')) {
            $upToDate = File::exists($path) && File::get($path) === $content;

            if (! $upToDate) {
                $this->error(AcceptanceReport::OUTPUT.' is not up to date. Run php artisan docs:acceptance.');
            }

            return $upToDate && $unknown === [] ? self::SUCCESS : self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->info('Acceptance report written.');

        return $unknown === [] ? self::SUCCESS : self::FAILURE;
    }
}
