<?php

namespace Modules\Reports\Actions;

use App\Support\Authorization\Permission;
use Modules\Reports\Support\ExportType;

class ExportVessels extends ExportAction
{
    public function permission(): Permission
    {
        return Permission::ReportsExportVessels;
    }

    protected function type(): ExportType
    {
        return ExportType::Vessels;
    }
}
