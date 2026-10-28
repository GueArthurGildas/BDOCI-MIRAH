<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AiAgentAction;
use Illuminate\View\View;

class AiAgentController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): View
    {
        return view('reports::ai-agent.index', [
            'requests' => AiAgentAction::with(['action', 'requester'])->latest('id')->paginate(self::PER_PAGE),
        ]);
    }
}
