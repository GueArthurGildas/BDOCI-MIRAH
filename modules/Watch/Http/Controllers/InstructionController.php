<?php

namespace Modules\Watch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StandingInstruction;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Watch\Actions\CreateStandingInstruction;
use Modules\Watch\Actions\EndStandingInstruction;
use Modules\Watch\Http\Requests\StoreStandingInstructionRequest;

class InstructionController extends Controller
{
    public function index(): View
    {
        return view('watch::instructions.index', [
            'instructions' => StandingInstruction::query()->with('vessel')->latest('started_on')->latest('id')->get(),
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
        ]);
    }

    public function store(StoreStandingInstructionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CreateStandingInstruction::class, $request->user(), $request->validated());

        return to_route('watch.instructions.index')->with('status', __('watch::watch.instructions.created'));
    }

    public function end(StandingInstruction $instruction, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(EndStandingInstruction::class, $request->user(), ['instruction_id' => $instruction->id]);

        return to_route('watch.instructions.index')->with('status', __('watch::watch.instructions.ended'));
    }
}
