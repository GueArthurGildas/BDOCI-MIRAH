<?php

namespace Modules\Movements\Http\Controllers;

use App\Enums\EezDeclarationStatus;
use App\Enums\EezDeclarationType;
use App\Enums\EezReason;
use App\Http\Controllers\Controller;
use App\Models\EezDeclaration;
use App\Models\EntryExitLink;
use App\Models\ReferenceList;
use App\Models\Species;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Movements\Actions\AcknowledgeEezDeclaration;
use Modules\Movements\Actions\CloseEezDeclaration;
use Modules\Movements\Actions\ExplainEntryExitVariance;
use Modules\Movements\Actions\RecordEezDeclaration;
use Modules\Movements\Http\Requests\ExplainVarianceRequest;
use Modules\Movements\Http\Requests\StoreEezDeclarationRequest;

class EezDeclarationController extends Controller
{
    private const PER_PAGE = 20;

    public const RECEPTION_CHANNELS_LIST = 'eez_reception_channels';

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(EezDeclarationType::class)],
            'status' => ['nullable', Rule::enum(EezDeclarationStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $declarations = EezDeclaration::query()
            ->with(['vessel', 'receptionChannel', 'enteredBy'])
            ->withSum('catches', 'weight_kg')
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('received_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('received_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereHas('vessel', fn (Builder $query) => $query->search($search))
                ->orWhereLike('declared_name', "%{$search}%")))
            ->latest('received_at')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('movements::eez.index', ['declarations' => $declarations, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('movements::eez.create', [
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'channels' => $this->receptionChannels(),
            'species' => Species::orderBy('name')->get()->mapWithKeys(fn (Species $species) => [$species->id => "{$species->fao_code} - {$species->name}"]),
            'reasons' => EezReason::options(),
        ]);
    }

    public function store(StoreEezDeclarationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $declaration = $actions->run(RecordEezDeclaration::class, $request->user(), $request->validated());

        return to_route('movements.eez.show', $declaration)->with('status', __('movements::eez.created'));
    }

    public function show(EezDeclaration $declaration): View
    {
        $declaration->load(['vessel', 'receptionChannel', 'enteredBy', 'catches.species', 'checks', 'alert', 'linkAsEntry.exit', 'linkAsExit.entry']);

        return view('movements::eez.show', ['declaration' => $declaration]);
    }

    public function acknowledge(EezDeclaration $declaration, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AcknowledgeEezDeclaration::class, $request->user(), ['eez_declaration_id' => $declaration->id]);

        return back()->with('status', __('movements::eez.acknowledged'));
    }

    public function close(EezDeclaration $declaration, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseEezDeclaration::class, $request->user(), ['eez_declaration_id' => $declaration->id]);

        return back()->with('status', __('movements::eez.closed'));
    }

    public function explain(EntryExitLink $link, ExplainVarianceRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(ExplainEntryExitVariance::class, $request->user(), ['entry_exit_link_id' => $link->id, ...$request->validated()]);

        return back()->with('status', __('movements::eez.explained'));
    }

    private function receptionChannels()
    {
        return ReferenceList::where('code', self::RECEPTION_CHANNELS_LIST)->first()
            ?->values()->where('is_active', true)->get()
            ->mapWithKeys(fn ($value) => [$value->id => $value->label]) ?? collect();
    }
}
