<?php

namespace Modules\Vms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ErsDeclaration;
use App\Models\Gear;
use App\Models\Presentation;
use App\Models\Species;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Vms\Actions\RecordErsDeclaration;

class ErsController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): View
    {
        return view('vms::ers.index', [
            'declarations' => ErsDeclaration::with('vessel')->withCount('lines')->withSum('lines', 'live_weight_kg')->latest('declared_at')->paginate(self::PER_PAGE),
        ]);
    }

    public function create(): View
    {
        return view('vms::ers.create', [
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'species' => Species::orderBy('name')->pluck('name', 'id'),
            'presentations' => Presentation::orderBy('label')->pluck('label', 'id'),
            'gears' => Gear::orderBy('label')->pluck('label', 'id'),
        ]);
    }

    public function store(Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'vessel_id' => ['required', 'exists:vessels,id'],
            'declared_at' => ['nullable', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.species_id' => ['required', 'exists:species,id'],
            'lines.*.presentation_id' => ['nullable', 'exists:presentations,id'],
            'lines.*.live_weight_kg' => ['required', 'numeric', 'min:0'],
            'lines.*.fao_zone' => ['nullable', 'string', 'max:10'],
            'lines.*.gear_id' => ['nullable', 'exists:gears,id'],
        ]);

        $declaration = $actions->run(RecordErsDeclaration::class, $request->user(), $data);

        return to_route('vms.ers.show', $declaration)->with('status', __('vms::vms.ers.recorded'));
    }

    public function show(ErsDeclaration $declaration): View
    {
        return view('vms::ers.show', ['declaration' => $declaration->load(['vessel', 'lines.species', 'lines.presentation', 'lines.gear'])]);
    }
}
