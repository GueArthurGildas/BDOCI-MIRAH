<?php

namespace Modules\Registry\Actions;

use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\Registry\Support\ResourceCatalog;

class UpdateRegistryRecord extends Action
{
    public function code(): string
    {
        return 'registry.update_record';
    }

    public function permission(): Permission
    {
        return Permission::RegistryManage;
    }

    public function handle(ActionContext $context, array $input): Model
    {
        $resource = ResourceCatalog::find($input['resource']);
        $record = $resource->model::findOrFail($input['id']);
        $record->update($input['attributes']);

        return $record;
    }
}
