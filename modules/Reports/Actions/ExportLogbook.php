<?php

namespace Modules\Reports\Actions;

use App\Support\Authorization\Permission;
use Modules\Reports\Support\ExportType;

class ExportLogbook extends ExportAction
{
    public function permission(): Permission
    {
        return Permission::ReportsExportLogbook;
    }

    protected function type(): ExportType
    {
        return ExportType::Logbook;
    }
}
