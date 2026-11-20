<?php

namespace Modules\Operations\Actions;

use App\Models\Document;
use App\Models\Operation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Validation\ValidationException;

class AttachOperationDocument extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'operations.attach_document';
    }

    public function permission(): Permission
    {
        return Permission::OperationsCreate;
    }

    public function handle(ActionContext $context, array $input): Operation
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if ($operation->isFinished()) {
            throw ValidationException::withMessages(['file' => __('operations::operations.errors.closed')]);
        }

        $this->documents->attach(Document::findOrFail($input['document_id']), $operation);

        return $operation;
    }
}
