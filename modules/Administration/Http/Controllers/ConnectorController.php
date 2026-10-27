<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConnectorController extends Controller
{
    private const LIST_LIMIT = 50;

    private const TABS = ['syncs', 'errors', 'rejections', 'comments'];

    public function index(): View
    {
        return view('administration::connectors.index', [
            'dataSources' => DataSource::withIssueCounts()->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, string $dataSource): View
    {
        $source = DataSource::withIssueCounts()->findOrFail($dataSource);

        return view('administration::connectors.show', [
            'dataSource' => $source,
            'tab' => $this->activeTab($request->query('tab'), $source),
            'limit' => self::LIST_LIMIT,
            'syncs' => $source->syncs()->limit(self::LIST_LIMIT)->get(),
            'errors' => $source->errors()->with(['sync', 'resolver'])->reorder()->orderByRaw('resolved_at is not null')->orderByDesc('occurred_at')->limit(self::LIST_LIMIT)->get(),
            'rejections' => $source->rejections()->with(['sync', 'handler'])->reorder()->orderByRaw("status = 'pending' desc")->orderByDesc('received_at')->limit(self::LIST_LIMIT)->get(),
            'comments' => $source->comments()->with('author')->limit(self::LIST_LIMIT)->get(),
        ]);
    }

    private function activeTab(mixed $requested, DataSource $source): string
    {
        if (in_array($requested, self::TABS, true)) {
            return $requested;
        }

        return match (true) {
            $source->open_errors_count > 0 => 'errors',
            $source->pending_rejections_count > 0 => 'rejections',
            default => 'comments',
        };
    }
}
