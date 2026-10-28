<?php

namespace Modules\Witnesses\Actions;

use App\Models\AuthorizedListDistribution;
use App\Models\AuthorizedListPublication;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordAuthorizedListDistribution extends Action
{
    public function code(): string
    {
        return 'witnesses.record_distribution';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesManage;
    }

    public function handle(ActionContext $context, array $input): AuthorizedListDistribution
    {
        return AuthorizedListDistribution::create([
            'authorized_list_publication_id' => AuthorizedListPublication::findOrFail($input['authorized_list_publication_id'])->id,
            'recipient' => $input['recipient'],
            'sent_at' => now(),
        ]);
    }
}
