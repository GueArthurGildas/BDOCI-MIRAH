<?php

namespace App\Http\Controllers;

use App\Support\Navigation\Navigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, Navigation $navigation): RedirectResponse
    {
        $firstItem = $navigation->groupsFor($request->user())->flatten(1)->first();

        return redirect($firstItem?->url() ?? route('dashboard'));
    }
}
