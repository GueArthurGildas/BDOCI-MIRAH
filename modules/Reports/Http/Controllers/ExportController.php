<?php

namespace Modules\Reports\Http\Controllers;

use App\Enums\ExportFormat;
use App\Enums\OperationStatus;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Reports\Actions\ExportLogbook;
use Modules\Reports\Actions\ExportOperations;
use Modules\Reports\Actions\ExportVessels;
use Modules\Reports\Http\Requests\ExportRequest;
use Modules\Reports\Support\ExportType;

class ExportController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): View
    {
        return view('reports::exports.index', [
            'types' => collect(ExportType::cases())->filter(fn (ExportType $type) => auth()->user()->can($type->permission()->value)),
            'formats' => ExportFormat::options(),
            'operationStatuses' => OperationStatus::options(),
            'recordStatuses' => RecordStatus::options(),
            'log' => ExportLog::with('user')->latest('exported_at')->latest('id')->paginate(self::PER_PAGE),
        ]);
    }

    public function download(ExportType $type, ExportRequest $request, ActionRunner $actions): Response
    {
        $action = match ($type) {
            ExportType::Operations => ExportOperations::class,
            ExportType::Vessels => ExportVessels::class,
            ExportType::Logbook => ExportLogbook::class,
        };

        $file = $actions->run($action, $request->user(), $request->validated());

        return response($file->content, 200, [
            'Content-Type' => $file->mimeType,
            'Content-Disposition' => 'attachment; filename="'.$file->fileName.'"',
        ]);
    }
}
