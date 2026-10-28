<?php

namespace Modules\Registry\Actions;

use App\Enums\SourceSystem;
use App\Models\SourcePriority;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Model;

class UpdateSourcePriorities extends Action
{
    public function code(): string
    {
        return 'registry.update_source_priorities';
    }

    public function permission(): Permission
    {
        return Permission::RegistryManage;
    }

    public function handle(ActionContext $context, array $input): ?Model
    {
        foreach ($input['ranks'] as $field => $ranks) {
            foreach ($ranks as $source => $rank) {
                SourcePriority::upsert(
                    [['field' => $field, 'source' => SourceSystem::from($source)->value, 'rank' => (int) $rank]],
                    ['field', 'source'],
                    ['rank'],
                );
            }
        }

        return null;
    }
}
