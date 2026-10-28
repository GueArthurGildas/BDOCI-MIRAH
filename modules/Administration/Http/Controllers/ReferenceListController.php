<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReferenceList;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\ReferenceListRequest;

class ReferenceListController extends Controller
{
    public function index(): View
    {
        return view('administration::reference-lists.index', [
            'lists' => ReferenceList::withCount('values')->orderBy('code')->get(),
        ]);
    }

    public function create(): View
    {
        return view('administration::reference-lists.create', ['list' => new ReferenceList]);
    }

    public function store(ReferenceListRequest $request): RedirectResponse
    {
        $list = ReferenceList::create($request->validated());

        return to_route('administration.reference-lists.edit', $list)->with('status', __('administration::reference_lists.created'));
    }

    public function edit(ReferenceList $referenceList): View
    {
        return view('administration::reference-lists.edit', [
            'list' => $referenceList->load('values'),
        ]);
    }

    public function update(ReferenceListRequest $request, ReferenceList $referenceList): RedirectResponse
    {
        $referenceList->update($request->validated());

        return to_route('administration.reference-lists.edit', $referenceList)->with('status', __('administration::reference_lists.updated'));
    }
}
