<?php

namespace Modules\Infractions\Http\Controllers;

use App\Enums\InfractionSeverity;
use App\Http\Controllers\Controller;
use App\Models\InfractionType;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Infractions\Actions\SaveInfractionType;
use Modules\Infractions\Http\Requests\TypeRequest;

class InfractionTypeController extends Controller
{
    public function index(): View
    {
        return view('infractions::types', [
            'types' => InfractionType::withCount('upheld')->orderBy('code')->get(),
            'severities' => InfractionSeverity::options(),
        ]);
    }

    public function store(TypeRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveInfractionType::class, $request->user(), $request->validated());

        return to_route('infractions.types.index')->with('status', __('infractions::infractions.messages.type_saved'));
    }

    public function update(InfractionType $type, TypeRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveInfractionType::class, $request->user(), [...$request->validated(), 'infraction_type_id' => $type->id]);

        return to_route('infractions.types.index')->with('status', __('infractions::infractions.messages.type_saved'));
    }
}
