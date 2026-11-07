<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InternalNote;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Dashboard\Support\Widget;
use Modules\Dashboard\Support\WidgetCatalog;

class DashboardController extends Controller
{
    private const MESSAGES_LIMIT = 5;

    public function __invoke(Request $request, WidgetCatalog $catalog): View
    {
        $user = $request->user();
        $widgets = $catalog->selectedFor($user)->map(fn (Widget $widget) => $catalog->data($widget))->groupBy(fn ($data) => $data->widget->kind);

        return view('dashboard::index', [
            'now' => now(),
            'kpis' => $widgets->get(Widget::KPI, collect()),
            'charts' => $widgets->get(Widget::CHART, collect()),
            'tables' => $widgets->get(Widget::TABLE, collect()),
            'messages' => InternalNote::addressedTo($user)->where('is_read', false)->with('author')->latest('id')->limit(self::MESSAGES_LIMIT)->get(),
            'unreadMessages' => InternalNote::addressedTo($user)->where('is_read', false)->count(),
        ]);
    }
}
