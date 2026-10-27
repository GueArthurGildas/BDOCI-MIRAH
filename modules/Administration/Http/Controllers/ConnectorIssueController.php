<?php

namespace Modules\Administration\Http\Controllers;

use App\Enums\RejectionStatus;
use App\Http\Controllers\Controller;
use App\Models\DataSourceError;
use App\Models\DataSourceRejection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConnectorIssueController extends Controller
{
    public function resolveError(Request $request, DataSourceError $error): RedirectResponse
    {
        $error->update(['resolved_at' => now(), 'resolved_by' => $request->user()->id]);

        return $this->backToConnector($error->data_source_id, 'errors', 'error_resolved');
    }

    public function reopenError(DataSourceError $error): RedirectResponse
    {
        $error->update(['resolved_at' => null, 'resolved_by' => null]);

        return $this->backToConnector($error->data_source_id, 'errors', 'error_reopened');
    }

    public function handleRejection(Request $request, DataSourceRejection $rejection): RedirectResponse
    {
        $status = RejectionStatus::from($request->validate([
            'status' => ['required', Rule::enum(RejectionStatus::class)],
        ])['status']);
        $isHandled = $status !== RejectionStatus::Pending;

        $rejection->update([
            'status' => $status,
            'handled_at' => $isHandled ? now() : null,
            'handled_by' => $isHandled ? $request->user()->id : null,
        ]);

        return $this->backToConnector($rejection->data_source_id, 'rejections', 'rejection_updated');
    }

    private function backToConnector(int $dataSourceId, string $tab, string $messageKey): RedirectResponse
    {
        return to_route('administration.connectors.show', [$dataSourceId, 'tab' => $tab])
            ->with('status', __("administration::administration.connectors.{$messageKey}"));
    }
}
