<?php

namespace Modules\Administration\Http\Controllers;

use App\Enums\DataSourceMode;
use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Support\Actions\ActionRunner;
use App\Support\Connectors\ConnectorRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Administration\Actions\ChangeConnectorMode;
use Modules\Administration\Actions\SynchroniseConnector;

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

    public function show(Request $request, string $dataSource, ConnectorRegistry $registry): View
    {
        $source = DataSource::withIssueCounts()->findOrFail($dataSource);

        return view('administration::connectors.show', [
            'dataSource' => $source,
            'tab' => $this->activeTab($request->query('tab'), $source),
            'limit' => self::LIST_LIMIT,
            'hasConnector' => $registry->has($source->code),
            'syncs' => $source->syncs()->limit(self::LIST_LIMIT)->get(),
            'errors' => $source->errors()->with(['sync', 'resolver'])->reorder()->orderByRaw('resolved_at is not null')->orderByDesc('occurred_at')->limit(self::LIST_LIMIT)->get(),
            'rejections' => $source->rejections()->with(['sync', 'handler'])->reorder()->orderByRaw("status = 'pending' desc")->orderByDesc('received_at')->limit(self::LIST_LIMIT)->get(),
            'comments' => $source->comments()->with('author')->limit(self::LIST_LIMIT)->get(),
        ]);
    }

    public function sync(Request $request, DataSource $dataSource, ActionRunner $actions, ConnectorRegistry $registry): RedirectResponse
    {
        abort_unless($registry->has($dataSource->code), 404);

        $input = ['data_source_id' => $dataSource->id];

        if ($dataSource->mode === DataSourceMode::Import) {
            $request->validate(['file' => ['required', 'file', 'extensions:json', 'max:5120']]);
            $input['file'] = Storage::disk('local')->path($request->file('file')->store(config('connectors.import_directory'), 'local'));
        }

        $sync = $actions->run(SynchroniseConnector::class, $request->user(), $input);

        return to_route('administration.connectors.show', [$dataSource, 'tab' => 'syncs'])
            ->with('status', __('administration::administration.connectors.synchronised', ['received' => $sync->received_count, 'rejected' => $sync->rejected_count]));
    }

    public function changeMode(Request $request, DataSource $dataSource, ActionRunner $actions): RedirectResponse
    {
        $validated = $request->validate(['mode' => ['required', Rule::enum(DataSourceMode::class)]]);

        $actions->run(ChangeConnectorMode::class, $request->user(), ['data_source_id' => $dataSource->id, 'mode' => $validated['mode']]);

        return to_route('administration.connectors.show', $dataSource)->with('status', __('administration::administration.connectors.mode_changed'));
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
