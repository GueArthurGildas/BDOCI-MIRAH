<?php

namespace Modules\Reports\Actions;

use App\Support\Authorization\Permission;
use Modules\Reports\Support\ExportType;

class ExportErsDeclarations extends ExportAction
{
    public function permission(): Permission
    {
        return Permission::ReportsExportErsDeclarations;
    }

    protected function type(): ExportType
    {
        return ExportType::ErsDeclarations;
    }
}
