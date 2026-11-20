<?php

namespace Modules\Operations\Http\Controllers;

use App\Enums\OperationStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookChannels;
use App\Support\Operations\WeatherScales;
use App\Support\Printing\PdfRenderer;
use App\Support\Printing\WordRenderer;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Operations\Actions\AddOperationEvent;
use Modules\Operations\Actions\AttachOperationDocument;
use Modules\Operations\Actions\CloseOperation;
use Modules\Operations\Actions\GiveOperationInstruction;
use Modules\Operations\Actions\OpenOperation;
use Modules\Operations\Actions\UpdateOperation;
use Modules\Operations\Http\Requests\AttachOperationDocumentRequest;
use Modules\Operations\Http\Requests\CloseOperationRequest;
use Modules\Operations\Http\Requests\StoreOperationEventRequest;
use Modules\Operations\Http\Requests\StoreOperationInstructionRequest;
use Modules\Operations\Http\Requests\StoreOperationRequest;
use Modules\Operations\Http\Requests\UpdateOperationRequest;
use Modules\Operations\Support\OperationDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'exists:operation_categories,id'],
            'status' => ['nullable', Rule::enum(OperationStatus::class)],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $operations = Operation::query()
            ->with(['category', 'vessels'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('code', "%{$search}%")
                ->orWhereLike('name', "%{$search}%")))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category_id', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['year'] ?? null, fn (Builder $query, string $year) => $query->where('year', $year))
            ->latest('started_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('operations::index', [
            'operations' => $operations,
            'filters' => $filters,
            'categories' => OperationCategory::orderBy('code')->get()->mapWithKeys(fn (OperationCategory $category) => [$category->id => strtoupper($category->code->value).' - '.$category->label]),
        ]);
    }

    public function create(WeatherScales $scales, LogbookChannels $channels): View
    {
        return view('operations::create', [
            'categories' => OperationCategory::orderBy('code')->get()->mapWithKeys(fn (OperationCategory $category) => [$category->id => strtoupper($category->code->value).' - '.$category->label]),
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'visibilities' => $scales->options(WeatherScales::VISIBILITY),
            'cloudCovers' => $scales->options(WeatherScales::CLOUD_COVER),
            'seaStates' => $scales->options(WeatherScales::SEA_STATE),
            'channels' => $channels->options(),
            'supervisors' => User::query()->where('is_active', true)->where('type', UserType::Human)->orderBy('name')->get()->mapWithKeys(fn (User $user) => [$user->id => $user->fullName()]),
        ]);
    }

    public function store(StoreOperationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $operation = $actions->run(OpenOperation::class, $request->user(), $request->validated());

        return to_route('operations.show', $operation)->with('status', __('operations::operations.opened'));
    }

    public function show(Operation $operation, LogbookChannels $channels, DocumentStore $documents): View
    {
        $operation->load([
            'category', 'context', 'events.channel', 'events.author', 'instructions.giver', 'personnel', 'logbookEntries.channel', 'logbookEntries.day', 'creator', 'closer',
            'vessels.flagCountry', 'vessels.vesselType', 'vessels.activeIdentifiers', 'vessels.actors.person', 'vessels.actors.legalEntity',
        ]);

        return view('operations::show', [
            'operation' => $operation,
            'channels' => $channels->options(),
            'documents' => $documents->for($operation),
        ]);
    }

    public function storeDocument(AttachOperationDocumentRequest $request, Operation $operation, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        $actions->run(AttachOperationDocument::class, $request->user(), [
            'operation_id' => $operation->id,
            'document_id' => $documents->store($request->file('file'), $request->user())->id,
        ]);

        return to_route('operations.show', $operation)->with('status', __('operations::operations.document_attached'));
    }

    public function word(Operation $operation, OperationDocument $document, WordRenderer $word): Response
    {
        $printed = PdfRenderer::inPrintLocale(fn () => $document->build($operation));

        return response(PdfRenderer::inPrintLocale(fn () => $word->render($printed)), 200, [
            'Content-Type' => WordRenderer::MIME_TYPE,
            'Content-Disposition' => 'attachment; filename="'.$printed->filename('docx').'"',
        ]);
    }

    public function pdf(Operation $operation, OperationDocument $document, PdfRenderer $pdf): Response
    {
        $printed = PdfRenderer::inPrintLocale(fn () => $document->build($operation));

        return response(PdfRenderer::inPrintLocale(fn () => $pdf->render($printed)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$printed->filename('pdf').'"',
        ]);
    }

    public function downloadDocument(Operation $operation, Document $document, DocumentStore $documents): StreamedResponse
    {
        abort_unless($documents->for($operation)->contains('id', $document->id), 404);

        return Storage::disk(DocumentStore::DISK)->download($document->path, $document->name);
    }

    public function update(UpdateOperationRequest $request, Operation $operation, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateOperation::class, $request->user(), [...$request->validated(), 'operation_id' => $operation->id]);

        return to_route('operations.show', $operation)->with('status', __('operations::operations.updated'));
    }

    public function close(CloseOperationRequest $request, Operation $operation, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseOperation::class, $request->user(), [...$request->validated(), 'operation_id' => $operation->id]);

        return to_route('operations.show', $operation)->with('status', __('operations::operations.closed'))->with('popup', true);
    }

    public function storeEvent(StoreOperationEventRequest $request, Operation $operation, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddOperationEvent::class, $request->user(), [...$request->validated(), 'operation_id' => $operation->id]);

        return to_route('operations.show', $operation)->with('status', __('operations::operations.event_added'));
    }

    public function storeInstruction(StoreOperationInstructionRequest $request, Operation $operation, ActionRunner $actions): RedirectResponse
    {
        $actions->run(GiveOperationInstruction::class, $request->user(), [...$request->validated(), 'operation_id' => $operation->id]);

        return to_route('operations.show', $operation)->with('status', __('operations::operations.instruction_given'));
    }
}
