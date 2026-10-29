<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OperationArchive;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Reports\Actions\ArchiveOperation;
use Modules\Reports\Support\ArchivableOperations;

class ArchiveController extends Controller
{
    private const PER_PAGE = 20;

    public function index(ArchivableOperations $archivable): View
    {
        return view('reports::archives.index', [
            'archives' => OperationArchive::with('operation.category')->latest('archived_at')->paginate(self::PER_PAGE),
            'eligibleCount' => $archivable->query()->count(),
            'years' => config('surveillance.archive_after_years'),
            'cutoff' => $archivable->cutoff(),
        ]);
    }

    public function run(ArchivableOperations $archivable, ActionRunner $actions): RedirectResponse
    {
        $ids = $archivable->query()->orderBy('id')->pluck('id');

        $ids->each(fn (int $id) => $actions->run(ArchiveOperation::class, request()->user(), ['operation_id' => $id]));

        return to_route('reports.archives.index')->with('status', trans_choice('reports::reports.archives.archived', $ids->count(), ['count' => $ids->count()]))->with('popup', true);
    }
}
