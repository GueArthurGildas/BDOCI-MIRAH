<?php

namespace Modules\Reports\Actions;

use App\Support\Authorization\Permission;
use Modules\Reports\Support\ExportType;

class ExportOperations extends ExportAction
{
    public function permission(): Permission
    {
        return Permission::ReportsExportOperations;
    }

    protected function type(): ExportType
    {
        return ExportType::Operations;
    }
}
