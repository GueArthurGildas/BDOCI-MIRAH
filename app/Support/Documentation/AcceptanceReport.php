<?php

namespace App\Support\Documentation;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;
use Tests\Attributes\Covers;

final class AcceptanceReport
{
    public const CATALOG = 'docs/ACCEPTATION.md';

    public const OUTPUT = 'docs/reference/acceptance.md';

    /** @return array<string, string> */
    public function catalog(): array
    {
        preg_match_all('/^\| ((?:UC|RG)\d{2}) \| ([^|]+) \|/m', File::get(base_path(self::CATALOG)), $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $match) => [$match[1] => trim($match[2])])->all();
    }

    /** @return array<string, array<int, array{test: string, partial: bool}>> */
    public function coverage(): array
    {
        $coverage = [];

        foreach ($this->testClasses() as $class) {
            $reflection = new ReflectionClass($class);

            foreach ($this->attributesOf($reflection) as $cover) {
                foreach ($this->testMethods($reflection) as $method) {
                    $this->record($coverage, $cover, $class.'::'.$method->getName());
                }
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class) {
                    continue;
                }

                foreach ($this->attributesOf($method) as $cover) {
                    $this->record($coverage, $cover, $class.'::'.$method->getName());
                }
            }
        }

        ksort($coverage);

        return $coverage;
    }

    /** @return array<int, string> */
    public function unknownIds(): array
    {
        return array_values(array_diff(array_keys($this->coverage()), array_keys($this->catalog())));
    }

    public function render(): string
    {
        $catalog = $this->catalog();
        $coverage = $this->coverage();

        $rows = collect($catalog)->map(function (string $title, string $id) use ($coverage) {
            $tests = $coverage[$id] ?? [];

            return sprintf('| %s | %s | %s | %s |', $id, $title, $this->status($tests), $this->testList($tests));
        });

        $uncovered = collect($catalog)->keys()->reject(fn (string $id) => isset($coverage[$id]))->all();
        $unknown = $this->unknownIds();

        return "<!-- Fichier généré par `php artisan docs:acceptance` : ne pas le modifier à la main. -->\n\n"
            ."# Couverture des cas d'usage et des règles de gestion\n\n"
            ."Relevée dans les attributs `#[Covers]` des tests ; les identifiants et intitulés viennent de `docs/ACCEPTATION.md`.\n\n"
            ."| Id | Intitulé | Statut | Tests |\n|---|---|---|---|\n".$rows->implode("\n")."\n\n"
            ."## Sans test\n\n".($uncovered === [] ? "Aucun.\n" : implode(', ', $uncovered)."\n")
            ."\n## Identifiants inconnus cités par un test\n\n".($unknown === [] ? "Aucun.\n" : implode(', ', $unknown)."\n");
    }

    /** @param array<int, array{test: string, partial: bool}> $tests */
    private function status(array $tests): string
    {
        if ($tests === []) {
            return 'à faire';
        }

        return collect($tests)->contains(fn (array $test) => ! $test['partial']) ? 'fait' : 'partiel';
    }

    /** @param array<int, array{test: string, partial: bool}> $tests */
    private function testList(array $tests): string
    {
        return $tests === [] ? '—' : collect($tests)->map(fn (array $test) => '`'.class_basename(strstr($test['test'], '::', true)).strstr($test['test'], '::').'`')->implode('<br>');
    }

    /**
     * @param  array<string, array<int, array{test: string, partial: bool}>>  $coverage
     * @param  array{ids: array<int, string>, partial: bool}  $cover
     */
    private function record(array &$coverage, array $cover, string $test): void
    {
        foreach ($cover['ids'] as $id) {
            $coverage[$id][] = ['test' => $test, 'partial' => $cover['partial']];
        }
    }

    /** @return Collection<int, array{ids: array<int, string>, partial: bool}> */
    private function attributesOf(ReflectionClass|ReflectionMethod $target): Collection
    {
        return collect($target->getAttributes(Covers::class))->map(function ($attribute) {
            $arguments = $attribute->getArguments();
            $partial = (bool) ($arguments['partial'] ?? false);
            unset($arguments['partial']);

            return ['ids' => array_values($arguments), 'partial' => $partial];
        });
    }

    /** @return Collection<int, ReflectionMethod> */
    private function testMethods(ReflectionClass $class): Collection
    {
        return collect($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method) => $method->getDeclaringClass()->getName() === $class->getName() && str_starts_with($method->getName(), 'test_'));
    }

    /** @return Collection<int, class-string> */
    private function testClasses(): Collection
    {
        $directories = array_filter([base_path('tests'), ...glob(base_path('modules/*/Tests'))], 'is_dir');

        return collect(Finder::create()->files()->in($directories)->name('*Test.php'))
            ->map(fn ($file) => $this->classOf($file->getRealPath()))
            ->filter(fn (?string $class) => $class !== null && class_exists($class))
            ->sort()
            ->values();
    }

    private function classOf(string $path): ?string
    {
        $source = File::get($path);

        return preg_match('/^namespace ([^;]+);/m', $source, $namespace) && preg_match('/^(?:final |abstract )?class (\w+)/m', $source, $class)
            ? $namespace[1].'\\'.$class[1]
            : null;
    }
}
