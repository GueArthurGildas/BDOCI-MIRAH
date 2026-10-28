<?php

namespace Modules\Licenses\Actions;

use App\Enums\LicenseOrigin;
use App\Models\License;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Licenses\Support\LicenseWriter;

class RecordLicense extends Action
{
    public function __construct(private readonly LicenseWriter $writer) {}

    public function code(): string
    {
        return 'licenses.record';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): License
    {
        return $this->writer->create($context, $input, LicenseOrigin::Manual);
    }
}
