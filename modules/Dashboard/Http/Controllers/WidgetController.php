<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Dashboard\Support\Widget;
use Modules\Dashboard\Support\WidgetCatalog;

class WidgetController extends Controller
{
    public function show(Request $request, WidgetCatalog $catalog, string $widget): View
    {
        $definition = $catalog->availableFor($request->user())->get($widget);
        abort_if($definition === null, 404);

        return view('dashboard::widget', [
            'data' => $catalog->data($definition),
            'sample' => $catalog->sample($definition),
            'detailUrl' => $definition->detailRoute && Route::has($definition->detailRoute)
                ? route($definition->detailRoute, array_filter([$definition->indicator]))
                : null,
        ]);
    }

    public function edit(Request $request, WidgetCatalog $catalog): View
    {
        $user = $request->user();

        return view('dashboard::customize', [
            'groups' => $catalog->availableFor($user)->groupBy('kind'),
            'selected' => $catalog->selectedFor($user)->keys()->all(),
            'kinds' => [Widget::KPI, Widget::CHART, Widget::TABLE],
        ]);
    }

    public function update(Request $request, WidgetCatalog $catalog): RedirectResponse
    {
        $available = $catalog->availableFor($request->user())->keys()->all();
        $data = $request->validate([
            'widgets' => ['nullable', 'array'],
            'widgets.*' => ['string', Rule::in($available)],
        ]);

        $request->user()->update(['dashboard_widgets' => array_values(array_intersect($available, $data['widgets'] ?? []))]);

        return to_route('dashboard')->with('status', __('dashboard::dashboard.customize.saved'));
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->user()->update(['dashboard_widgets' => null]);

        return to_route('dashboard')->with('status', __('dashboard::dashboard.customize.reset_done'));
    }
}
