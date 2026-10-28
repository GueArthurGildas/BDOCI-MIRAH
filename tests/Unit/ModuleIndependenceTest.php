<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ModuleIndependenceTest extends TestCase
{
    public function test_a_module_never_uses_the_code_of_another_module(): void
    {
        $violations = [];

        foreach (glob(dirname(__DIR__, 2).'/modules/*', GLOB_ONLYDIR) as $moduleDirectory) {
            $module = basename($moduleDirectory);

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($moduleDirectory, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/Tests/')) {
                    continue;
                }

                preg_match_all('/Modules\\\\(\w+)\\\\/', file_get_contents($file->getPathname()), $matches);

                foreach (array_unique($matches[1]) as $referenced) {
                    if ($referenced !== $module) {
                        $violations[] = "{$module} uses {$referenced} in ".basename($file->getPathname());
                    }
                }
            }
        }

        $this->assertSame([], $violations);
    }
}
