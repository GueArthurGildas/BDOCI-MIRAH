<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LicenseImport;
use App\Models\LicenseImportLine;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Licenses\Actions\CancelLicenseImport;
use Modules\Licenses\Actions\ImportLicenseFile;
use Modules\Licenses\Actions\StartLicenseBatch;
use Modules\Licenses\Actions\UpdateLicenseImportLine;
use Modules\Licenses\Actions\ValidateLicenseImport;
use Modules\Licenses\Actions\ValidateLicenseImportLine;
use Modules\Licenses\Http\Requests\ImportFileRequest;
use Modules\Licenses\Http\Requests\ImportLineRequest;
use Modules\Licenses\Http\Requests\StartBatchRequest;
use Modules\Licenses\Support\CsvReader;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('licenses::imports.index', [
            'imports' => LicenseImport::query()->with('creator')->withCount([
                'lines',
                'lines as rejected_count' => fn ($query) => $query->where('status', 'rejected'),
                'lines as valid_count' => fn ($query) => $query->where('status', 'valid'),
            ])->latest('id')->paginate(15),
            'withoutLicense' => Vessel::query()->where('category', 'fishing')->whereDoesntHave('licenses')->orderBy('name')->get()
                ->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"])->all(),
            'columns' => CsvReader::COLUMNS,
        ]);
    }

    public function show(LicenseImport $import): View
    {
        return view('licenses::imports.show', [
            'import' => $import->load(['creator', 'document']),
            'lines' => $import->lines()->with('vessel')->get(),
        ]);
    }

    public function storeFile(ImportFileRequest $request, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        $import = $actions->run(ImportLicenseFile::class, $request->user(), ['document_id' => $documents->store($request->file('file'), $request->user())->id]);

        return to_route('licenses.imports.show', $import)->with('status', __('licenses::imports.uploaded'));
    }

    public function storeBatch(StartBatchRequest $request, ActionRunner $actions): RedirectResponse
    {
        $import = $actions->run(StartLicenseBatch::class, $request->user(), ['vessel_ids' => array_map('intval', $request->validated('vessel_ids'))]);

        return to_route('licenses.imports.show', $import)->with('status', __('licenses::imports.batch_started'));
    }

    public function updateLine(LicenseImportLine $line, ImportLineRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateLicenseImportLine::class, $request->user(), ['line_id' => $line->id, 'data' => $request->lineData()]);

        return to_route('licenses.imports.show', $line->license_import_id)->with('status', __('licenses::imports.line_saved'));
    }

    public function validateLine(LicenseImportLine $line, ImportLineRequest $request, ActionRunner $actions): RedirectResponse
    {
        $result = $actions->run(ValidateLicenseImportLine::class, $request->user(), ['line_id' => $line->id, 'data' => $request->lineData()]);

        return to_route('licenses.imports.show', $line->license_import_id)->with('status', __($result->status->value === 'valid' ? 'licenses::imports.line_validated' : 'licenses::imports.line_rejected'));
    }

    public function validateAll(LicenseImport $import, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(ValidateLicenseImport::class, $request->user(), ['import_id' => $import->id]);

        return to_route('licenses.imports.show', $import)->with('status', __('licenses::imports.batch_validated'));
    }

    public function cancel(LicenseImport $import, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CancelLicenseImport::class, $request->user(), ['import_id' => $import->id]);

        return to_route('licenses.imports.show', $import)->with('status', __('licenses::imports.cancelled'));
    }
}
