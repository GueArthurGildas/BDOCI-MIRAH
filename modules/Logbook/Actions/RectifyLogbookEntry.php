<?php

namespace Modules\Logbook\Actions;

use App\Models\LogbookEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookWriter;
use Illuminate\Validation\ValidationException;

class RectifyLogbookEntry extends Action
{
    public function __construct(private readonly LogbookWriter $writer) {}

    public function code(): string
    {
        return 'logbook.rectify_entry';
    }

    public function permission(): Permission
    {
        return Permission::LogbookCreate;
    }

    public function handle(ActionContext $context, array $input): LogbookEntry
    {
        $original = LogbookEntry::with('vessels')->findOrFail($input['entry_id']);

        if ($original->corrects_entry_id !== null) {
            throw ValidationException::withMessages(['content' => __('logbook::logbook.errors.already_a_correction')]);
        }

        return $this->writer->write($context, [
            'channel_id' => $original->channel_id,
            'direction' => $original->direction,
            'content' => $input['content'],
            'is_order_or_decision' => $original->is_order_or_decision,
            'operation_id' => $original->operation_id,
            'vessel_ids' => $original->vessels->pluck('id')->all(),
            'corrects_entry_id' => $original->id,
        ]);
    }
}
