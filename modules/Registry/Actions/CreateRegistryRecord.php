<?php

namespace Modules\Registry\Actions;

use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Model;
use Modules\Registry\Support\ResourceCatalog;

class CreateRegistryRecord extends Action
{
    public function code(): string
    {
        return 'registry.create_record';
    }

    public function permission(): Permission
    {
        return Permission::RegistryManage;
    }

    public function handle(ActionContext $context, array $input): Model
    {
        $resource = ResourceCatalog::find($input['resource']);

        return $resource->model::create($input['attributes']);
    }
}
