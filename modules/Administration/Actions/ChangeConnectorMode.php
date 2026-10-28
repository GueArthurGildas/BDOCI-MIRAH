<?php

namespace Modules\Administration\Actions;

use App\Enums\DataSourceMode;
use App\Models\DataSource;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class ChangeConnectorMode extends Action
{
    public function code(): string
    {
        return 'administration.change_connector_mode';
    }

    public function permission(): Permission
    {
        return Permission::ConnectorsManage;
    }

    public function handle(ActionContext $context, array $input): DataSource
    {
        $source = DataSource::findOrFail($input['data_source_id']);
        $source->update(['mode' => DataSourceMode::from($input['mode'])]);

        return $source;
    }
}
