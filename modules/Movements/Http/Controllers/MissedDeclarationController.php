<?php

namespace Modules\Movements\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MissedDeclaration;
use App\Support\Actions\ActionRunner;
use App\Support\Tables\PerPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Movements\Actions\QualifyMissedDeclaration;
use Modules\Movements\Actions\RemindMissedDeclaration;
use Modules\Movements\Http\Requests\QualifyMissedDeclarationRequest;

class MissedDeclarationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate(['resolved' => ['nullable', 'in:0,1']]);

        $missed = MissedDeclaration::query()
            ->with('vessel')
            ->when(($filters['resolved'] ?? '0') === '0', fn ($query) => $query->unresolved(), fn ($query) => $query->whereNotIn('id', MissedDeclaration::unresolved()->select('missed_declarations.id')))
            ->latest('detected_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('movements::eez.missed', ['missed' => $missed, 'filters' => $filters]);
    }

    public function qualify(MissedDeclaration $missed, QualifyMissedDeclarationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(QualifyMissedDeclaration::class, $request->user(), ['missed_declaration_id' => $missed->id, ...$request->validated()]);

        return back()->with('status', __('movements::eez.missed_qualified'));
    }

    public function remind(MissedDeclaration $missed, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RemindMissedDeclaration::class, $request->user(), ['missed_declaration_id' => $missed->id]);

        return back()->with('status', __('movements::eez.missed_reminded'));
    }
}
