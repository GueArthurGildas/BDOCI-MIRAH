<?php

namespace Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Support\Vms\ThemisLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThermisController extends Controller
{
    public function __invoke(Request $request, ThemisLink $links): View
    {
        $filters = $request->validate([
            'vessels' => ['nullable', 'array', 'max:50'],
            'vessels.*' => ['integer', 'exists:vessels,id'],
        ]);

        $selected = Vessel::whereIn('id', $filters['vessels'] ?? [])->orderBy('name')->get();

        return view('tracking::thermis', [
            'baseUrl' => config('surveillance.vms.themis_url'),
            'vesselUrlConfigured' => (bool) config('surveillance.vms.themis_vessel_url'),
            'options' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => $vessel->name.' ('.$vessel->registration_number.')']),
            'selectedIds' => $selected->pluck('id')->all(),
            'targets' => $selected->map(fn (Vessel $vessel) => ['vessel' => $vessel, 'url' => $links->url($vessel)]),
        ]);
    }
}
