<?php

namespace Modules\Watch\Actions;

use App\Models\Rotation;
use App\Models\ShiftHandover;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Auth\Access\AuthorizationException;

class StartShiftHandover extends Action
{
    public function code(): string
    {
        return 'watch.start_handover';
    }

    public function permission(): Permission
    {
        return Permission::WatchCreate;
    }

    public function handle(ActionContext $context, array $input): ShiftHandover
    {
        $rotation = Rotation::with('team')->findOrFail($input['rotation_id']);

        if ($rotation->team_id !== $context->actor->team_id && $rotation->team->lead_user_id !== $context->actor->id) {
            throw new AuthorizationException;
        }

        return ShiftHandover::firstOrCreate(['rotation_id' => $rotation->id], ['started_at' => now()]);
    }
}
