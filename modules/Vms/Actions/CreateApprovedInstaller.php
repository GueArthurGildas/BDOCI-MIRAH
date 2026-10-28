<?php

namespace Modules\Vms\Actions;

use App\Models\ApprovedInstaller;
use App\Models\LegalEntity;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class CreateApprovedInstaller extends Action
{
    public function code(): string
    {
        return 'vms.create_installer';
    }

    public function permission(): Permission
    {
        return Permission::VmsManage;
    }

    public function handle(ActionContext $context, array $input): ApprovedInstaller
    {
        $entity = isset($input['legal_entity_id']) ? LegalEntity::findOrFail($input['legal_entity_id']) : LegalEntity::create(['name' => $input['name']]);

        return ApprovedInstaller::create(['legal_entity_id' => $entity->id, 'approved_until' => $input['approved_until'] ?? null]);
    }
}
