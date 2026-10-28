<?php

namespace App\Support\Modules;

use App\Models\RegisteredModule;

final class ModuleRegistry
{
    /** @var array<string, array{label: string, version: string, dependencies: array<int, string>}> */
    private array $declared = [];

    /** @var array<string, int> */
    private array $ids = [];

    /** @param array<int, string> $dependencies */
    public function declare(string $code, string $label, string $version = '1.0.0', array $dependencies = []): void
    {
        $this->declared[$code] = ['label' => $label, 'version' => $version, 'dependencies' => $dependencies];
    }

    /** @return array<string, array{label: string, version: string, dependencies: array<int, string>}> */
    public function declared(): array
    {
        return $this->declared;
    }

    public function sync(): int
    {
        foreach (array_keys($this->declared) as $code) {
            $this->idFor($code);
        }

        return count($this->declared);
    }

    public function idFor(string $code): int
    {
        return $this->ids[$code] ??= RegisteredModule::updateOrCreate(['code' => $code], $this->attributesFor($code))->id;
    }

    /** @return array{label: string, version: string, dependencies: array<int, string>} */
    private function attributesFor(string $code): array
    {
        return $this->declared[$code] ?? ['label' => $code, 'version' => '1.0.0', 'dependencies' => []];
    }
}
