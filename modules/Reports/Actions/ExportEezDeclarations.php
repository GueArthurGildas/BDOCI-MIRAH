<?php

namespace Modules\Reports\Actions;

use App\Support\Authorization\Permission;
use Modules\Reports\Support\ExportType;

class ExportEezDeclarations extends ExportAction
{
    public function permission(): Permission
    {
        return Permission::ReportsExportEezDeclarations;
    }

    protected function type(): ExportType
    {
        return ExportType::EezDeclarations;
    }
}
