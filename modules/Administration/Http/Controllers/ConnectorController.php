<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use Illuminate\View\View;

class ConnectorController extends Controller
{
    public function __invoke(): View
    {
        return view('administration::connectors', ['dataSources' => DataSource::orderBy('name')->get()]);
    }
}
