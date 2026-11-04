<?php

namespace Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tracking\Support\ThermisPanel;

class ThermisController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('tracking::thermis', ['panel' => ThermisPanel::for($request)]);
    }
}
