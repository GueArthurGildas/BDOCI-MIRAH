<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReferenceList;
use App\Models\ReferenceListValue;
use Illuminate\Http\RedirectResponse;
use Modules\Administration\Http\Requests\ReferenceListValueRequest;

class ReferenceListValueController extends Controller
{
    public function store(ReferenceListValueRequest $request, ReferenceList $referenceList): RedirectResponse
    {
        $referenceList->values()->create([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => true,
        ]);

        return to_route('administration.reference-lists.edit', $referenceList)->with('status', __('administration::reference_lists.value_added'));
    }

    public function update(ReferenceListValueRequest $request, ReferenceList $referenceList, ReferenceListValue $value): RedirectResponse
    {
        $value->update([
            'label' => $request->validated('label'),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('administration.reference-lists.edit', $referenceList)->with('status', __('administration::reference_lists.value_updated'));
    }
}
