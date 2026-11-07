<?php

namespace Modules\Reports\Support;

use App\Support\Authorization\Permission;

enum ExportType: string
{
    case Operations = 'operations';
    case Vessels = 'vessels';
    case Logbook = 'logbook';
    case EezDeclarations = 'eez_declarations';
    case ErsDeclarations = 'ers_declarations';

    public function permission(): Permission
    {
        return match ($this) {
            self::Operations => Permission::ReportsExportOperations,
            self::Vessels => Permission::ReportsExportVessels,
            self::Logbook => Permission::ReportsExportLogbook,
            self::EezDeclarations => Permission::ReportsExportEezDeclarations,
            self::ErsDeclarations => Permission::ReportsExportErsDeclarations,
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
            self::EezDeclarations, self::ErsDeclarations => ['from', 'to', 'vessel'],
        };
    }
}
