<?php

namespace Modules\Administration\Http\Controllers;

use App\Enums\CustomFieldType;
use App\Http\Controllers\Controller;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\CustomFieldRequest;

class CustomFieldController extends Controller
{
    public function index(): View
    {
        return view('administration::custom-fields.index', [
            'fields' => CustomFieldDefinition::orderBy('object_type')->orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        return view('administration::custom-fields.create', ['field' => new CustomFieldDefinition(['is_visible' => true]), ...$this->formData()]);
    }

    public function store(CustomFieldRequest $request): RedirectResponse
    {
        CustomFieldDefinition::create([...$request->validated(), 'is_visible' => $request->boolean('is_visible')]);

        return to_route('administration.custom-fields.index')->with('status', __('administration::custom_fields.created'));
    }

    public function edit(CustomFieldDefinition $customField): View
    {
        return view('administration::custom-fields.edit', ['field' => $customField, ...$this->formData()]);
    }

    public function update(CustomFieldRequest $request, CustomFieldDefinition $customField): RedirectResponse
    {
        $customField->update(['label' => $request->validated('label'), 'is_visible' => $request->boolean('is_visible')]);

        return to_route('administration.custom-fields.index')->with('status', __('administration::custom_fields.updated'));
    }

    /** @return array{objects: array<string, string>, types: array<string, string>} */
    private function formData(): array
    {
        return [
            'objects' => collect(config('custom-fields.objects'))
                ->mapWithKeys(fn (string $key, string $class) => [$class => __("administration::custom_fields.objects.{$key}")])
                ->all(),
            'types' => collect(CustomFieldType::cases())->mapWithKeys(fn (CustomFieldType $type) => [$type->value => $type->label()])->all(),
        ];
    }
}
