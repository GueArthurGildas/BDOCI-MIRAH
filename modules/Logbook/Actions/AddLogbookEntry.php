<?php

namespace Modules\Logbook\Actions;

use App\Models\LogbookEntry;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookWriter;

class AddLogbookEntry extends Action
{
    public function __construct(private readonly LogbookWriter $writer) {}

    public function code(): string
    {
        return 'logbook.add_entry';
    }

    public function permission(): Permission
    {
        return Permission::LogbookCreate;
    }

    public function handle(ActionContext $context, array $input): LogbookEntry
    {
        return $this->writer->write($context, $input);
    }
}
