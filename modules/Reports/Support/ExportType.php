<?php

namespace Modules\Reports\Support;

use App\Support\Authorization\Permission;

enum ExportType: string
{
    case Operations = 'operations';
    case Vessels = 'vessels';
    case Logbook = 'logbook';

    public function permission(): Permission
    {
        return match ($this) {
            self::Operations => Permission::ReportsExportOperations,
            self::Vessels => Permission::ReportsExportVessels,
            self::Logbook => Permission::ReportsExportLogbook,
        };
    }

    public function label(): string
    {
        return "reports::reports.exports.types.{$this->value}";
    }

    /** @return array<int, string> */
    public function filterKeys(): array
    {
        return match ($this) {
            self::Operations => ['year', 'status'],
            self::Vessels => ['search', 'record_status'],
            self::Logbook => ['from', 'to'],
        };
    }
}
