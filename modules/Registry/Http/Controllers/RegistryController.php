<?php

namespace Modules\Registry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Registry\Actions\CreateRegistryRecord;
use Modules\Registry\Actions\UpdateRegistryRecord;
use Modules\Registry\Support\ResourceCatalog;

class RegistryController extends Controller
{
    private const PER_PAGE = 25;

    public function index(): View
    {
        $resources = ResourceCatalog::all()->map(fn ($resource) => [
            'resource' => $resource,
            'count' => $resource->model::count(),
        ]);

        return view('registry::index', compact('resources'));
    }

    public function list(string $resource): View
    {
        $definition = ResourceCatalog::find($resource);

        return view('registry::list', [
            'resource' => $definition,
            'records' => $definition->query()->paginate(self::PER_PAGE),
        ]);
    }

    public function create(string $resource): View
    {
        $definition = ResourceCatalog::find($resource);

        return view('registry::form', ['resource' => $definition, 'record' => new $definition->model]);
    }

    public function store(Request $request, string $resource, ActionRunner $actions): RedirectResponse
    {
        $definition = ResourceCatalog::find($resource);
        $attributes = $request->validate($definition->rules());

        $actions->run(CreateRegistryRecord::class, $request->user(), ['resource' => $resource, 'attributes' => $attributes]);

        return to_route('registry.list', $resource)->with('status', __('registry::registry.created'));
    }

    public function edit(string $resource, int $id): View
    {
        $definition = ResourceCatalog::find($resource);

        return view('registry::form', ['resource' => $definition, 'record' => $definition->model::findOrFail($id)]);
    }

    public function update(Request $request, string $resource, int $id, ActionRunner $actions): RedirectResponse
    {
        $definition = ResourceCatalog::find($resource);
        $record = $definition->model::findOrFail($id);
        $attributes = $request->validate($definition->rules($record));

        $actions->run(UpdateRegistryRecord::class, $request->user(), ['resource' => $resource, 'id' => $id, 'attributes' => $attributes]);

        return to_route('registry.list', $resource)->with('status', __('registry::registry.updated'));
    }
}
