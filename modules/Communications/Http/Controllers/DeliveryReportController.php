<?php

namespace Modules\Communications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\JsonResponse;
use Modules\Communications\Actions\RecordDeliveryReport;
use Modules\Communications\Http\Requests\DeliveryReportRequest;
use Modules\Communications\Support\SystemActor;

class DeliveryReportController extends Controller
{
    public function __invoke(DeliveryReportRequest $request, ActionRunner $actions, SystemActor $system): JsonResponse
    {
        $message = $actions->run(RecordDeliveryReport::class, $system->resolve(), $request->validated());

        return response()->json(['message_id' => $message->id, 'status' => $message->status->value]);
    }
}
