<?php

namespace Modules\Administration\Actions;

use App\Enums\ExternalLinkSource;
use App\Models\ExternalLink;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class UpdateExternalLink extends Action
{
    public function code(): string
    {
        return 'administration.update_external_link';
    }

    public function permission(): Permission
    {
        return Permission::ConnectorsManage;
    }

    public function handle(ActionContext $context, array $input): ExternalLink
    {
        $link = ExternalLink::firstOrNew(['source' => ExternalLinkSource::from($input['source'])]);
        $link->fill([
            'model_url' => $input['model_url'] ?? null,
            'is_copy_fallback' => (bool) $input['is_copy_fallback'],
        ])->save();

        return $link;
    }
}
