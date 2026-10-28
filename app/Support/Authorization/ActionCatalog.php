<?php

namespace App\Support\Authorization;

use App\Models\ActionCatalogEntry;
use App\Support\Modules\ModuleRegistry;

final class ActionCatalog
{
    /** @var array<string, int> */
    private array $ids = [];

    public function __construct(private readonly ModuleRegistry $modules) {}

    public function sync(): int
    {
        foreach (Permission::cases() as $permission) {
            $this->ids[$permission->value] = ActionCatalogEntry::updateOrCreate(
                ['code' => $permission->value],
                ['module_id' => $this->modules->idFor($permission->module()), 'verb' => $permission->verb()],
            )->id;
        }

        return count(Permission::cases());
    }

    public function idFor(Permission|string $permission): int
    {
        $code = $permission instanceof Permission ? $permission->value : $permission;

        return $this->ids[$code] ??= ActionCatalogEntry::where('code', $code)->value('id') ?? $this->syncAndFind($code);
    }

    private function syncAndFind(string $code): int
    {
        $this->sync();

        return $this->ids[$code] ?? throw new \InvalidArgumentException("Unknown action: {$code}");
    }
}
