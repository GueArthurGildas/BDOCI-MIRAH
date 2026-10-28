<?php

namespace Modules\Communications\Actions;

use App\Models\InternalNote;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;

class MarkNoteRead extends Action
{
    public function code(): string
    {
        return 'communications.mark_note_read';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsView;
    }

    public function handle(ActionContext $context, array $input): InternalNote
    {
        $note = InternalNote::lockForUpdate()->findOrFail($input['note_id']);

        if (! $note->isAddressedTo($context->actor)) {
            throw new AuthorizationException;
        }

        if (! $note->is_read) {
            $note->update(['is_read' => true, 'read_at' => now()]);
        }

        return $note;
    }
}
