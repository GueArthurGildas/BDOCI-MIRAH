<?php

namespace App\Console\Commands;

use App\Support\Documentation\ReferenceGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateReferenceDocs extends Command
{
    protected $signature = 'docs:generate {--check : Fail when the generated files are not up to date}';

    protected $description = 'Generate the reference documentation (modules, actions, roles, routes, schema)';

    public function handle(ReferenceGenerator $generator): int
    {
        $outdated = [];

        foreach ($generator->files() as $path => $content) {
            $absolute = base_path($path);

            if (File::exists($absolute) && File::get($absolute) === $content) {
                continue;
            }

            $outdated[] = $path;

            if (! $this->option('check')) {
                File::ensureDirectoryExists(dirname($absolute));
                File::put($absolute, $content);
            }
        }

        if ($this->option('check')) {
            foreach ($outdated as $path) {
                $this->error("{$path} is not up to date. Run php artisan docs:generate.");
            }

            return $outdated === [] ? self::SUCCESS : self::FAILURE;
        }

        $this->info(count($outdated).' file(s) written.');

        return self::SUCCESS;
    }
}
