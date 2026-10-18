<?php

namespace Tests\Unit;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TranslationCompletenessTest extends TestCase
{
    private const SHARED_APPLICATION_FILES = ['ui.php', 'enums.php'];

    #[DataProvider('translationGroups')]
    public function test_every_locale_defines_the_same_keys(string $directory, string $file): void
    {
        $keysByLocale = array_map(
            fn (string $localeDirectory) => $this->keys("{$localeDirectory}/{$file}"),
            glob("{$directory}/*", GLOB_ONLYDIR),
        );

        foreach ($keysByLocale as $keys) {
            $this->assertEqualsCanonicalizing($keysByLocale[0], $keys, "Translation keys differ in {$directory}/*/{$file}");
        }
    }

    public static function translationGroups(): iterable
    {
        $root = dirname(__DIR__, 2);
        $directories = [$root.'/lang', ...glob($root.'/modules/*/lang')];

        foreach ($directories as $directory) {
            foreach (glob("{$directory}/en/*.php") as $path) {
                $file = basename($path);

                if ($directory === $root.'/lang' && ! in_array($file, self::SHARED_APPLICATION_FILES, true)) {
                    continue;
                }

                yield str_replace($root.'/', '', "{$directory}/{$file}") => [$directory, $file];
            }
        }
    }

    private function keys(string $path): array
    {
        $this->assertFileExists($path);

        return array_keys(Arr::dot(require $path));
    }
}
