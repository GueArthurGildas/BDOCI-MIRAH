<?php

namespace Modules\Mrep\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MrepGiesRefusal;
use App\Models\MrepTargeting;
use Illuminate\View\View;

class MirrorListController extends Controller
{
    private const PER_PAGE = 20;

    public function refusals(): View
    {
        return view('mrep::refusals', ['refusals' => MrepGiesRefusal::with('vessel')->latest('refused_on')->paginate(self::PER_PAGE)]);
    }

    public function targetings(): View
    {
        return view('mrep::targetings', ['targetings' => MrepTargeting::orderByDesc('year')->orderBy('target')->get()]);
    }
}
