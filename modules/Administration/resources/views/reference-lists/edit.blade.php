<x-layouts.app :title="__('administration::reference_lists.edit_title')">
    <x-ui.page-header :title="$list->label" :subtitle="$list->code" :breadcrumbs="[__('administration::reference_lists.title') => route('administration.reference-lists.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('administration::reference_lists.edit_title')">
            <form method="POST" action="{{ route('administration.reference-lists.update', $list) }}">
                @csrf
                @method('PUT')
                <x-ui.form.input name="code" :label="__('administration::reference_lists.code')" :value="$list->code" disabled />
                <x-ui.form.input name="label" :label="__('administration::reference_lists.label')" :value="$list->label" maxlength="120" required />

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> {{ __('ui.save') }}</button>
                    <a href="{{ route('administration.reference-lists.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :title="__('administration::reference_lists.values_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::reference_lists.code') }}</th>
                    <th>{{ __('administration::reference_lists.label') }}</th>
                    <th>{{ __('administration::reference_lists.sort_order') }}</th>
                    <th>{{ __('administration::reference_lists.active') }}</th>
                    <th></th>
                </x-slot:head>
                @foreach ($list->values as $value)
                    <tr>
                        <td><code>{{ $value->code }}</code></td>
                        <td><input type="text" name="label" form="value-{{ $value->id }}" value="{{ $value->label }}" maxlength="160" required class="form-control form-control-sm"></td>
                        <td><input type="number" name="sort_order" form="value-{{ $value->id }}" value="{{ $value->sort_order }}" min="0" class="form-control form-control-sm"></td>
                        <td>
                            <input type="hidden" name="is_active" form="value-{{ $value->id }}" value="0">
                            <input type="checkbox" name="is_active" form="value-{{ $value->id }}" value="1" @checked($value->is_active)>
                        </td>
                        <td class="text-end">
                            <form id="value-{{ $value->id }}" method="POST" action="{{ route('administration.reference-lists.values.update', [$list, $value]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('ui.save') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td><input type="text" name="code" form="value-new" maxlength="60" required placeholder="{{ __('administration::reference_lists.code') }}" class="form-control form-control-sm @error('code') is-invalid @enderror"></td>
                    <td><input type="text" name="label" form="value-new" maxlength="160" required placeholder="{{ __('administration::reference_lists.label') }}" class="form-control form-control-sm"></td>
                    <td><input type="number" name="sort_order" form="value-new" min="0" value="0" class="form-control form-control-sm"></td>
                    <td></td>
                    <td class="text-end">
                        <form id="value-new" method="POST" action="{{ route('administration.reference-lists.values.store', $list) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary"><i class="feather icon-plus"></i> {{ __('administration::reference_lists.add_value') }}</button>
                        </form>
                    </td>
                </tr>
            </x-ui.table>
            @error('code')
                <div class="text-danger f-12 p-3">{{ $message }}</div>
            @enderror
        </x-ui.card>
    </div>
</x-layouts.app>
