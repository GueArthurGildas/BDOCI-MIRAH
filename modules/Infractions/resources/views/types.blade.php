<x-layouts.app :title="__('infractions::infractions.nomenclature')">
    <x-ui.page-header :title="__('infractions::infractions.nomenclature')" :subtitle="__('infractions::infractions.nomenclature_subtitle')" icon="icon-list" :breadcrumbs="[__('infractions::infractions.title') => route('infractions.index')]" />

    <div class="page-body">
        @foreach ($types as $type)
            <form method="POST" action="{{ route('infractions.types.update', $type) }}" id="type-{{ $type->id }}">
                @csrf
                @method('PUT')
            </form>
        @endforeach

        <x-ui.card :title="__('infractions::infractions.nomenclature')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('infractions::infractions.fields.code') }}</th>
                    <th>{{ __('infractions::infractions.fields.label') }}</th>
                    <th>{{ __('infractions::infractions.fields.legal_basis') }}</th>
                    <th>{{ __('infractions::infractions.fields.severity') }}</th>
                    <th>{{ __('infractions::infractions.fields.potential_penalty') }}</th>
                    <th>{{ __('infractions::infractions.fields.cases_count') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($types as $type)
                    <tr>
                        <td><input form="type-{{ $type->id }}" name="code" value="{{ $type->code }}" class="form-control form-control-sm" required></td>
                        <td><input form="type-{{ $type->id }}" name="label" value="{{ $type->label }}" class="form-control form-control-sm" required></td>
                        <td><input form="type-{{ $type->id }}" name="legal_basis" value="{{ $type->legal_basis }}" class="form-control form-control-sm"></td>
                        <td>
                            <select form="type-{{ $type->id }}" name="severity" class="form-select form-select-sm">
                                <option value="">—</option>
                                @foreach ($severities as $value => $label)
                                    <option value="{{ $value }}" @selected($type->severity === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input form="type-{{ $type->id }}" name="potential_penalty" value="{{ $type->potential_penalty }}" class="form-control form-control-sm"></td>
                        <td>
                            {{ $type->upheld_count }}
                            <input type="hidden" form="type-{{ $type->id }}" name="is_active" value="0">
                            <label class="m-l-10"><input type="checkbox" form="type-{{ $type->id }}" name="is_active" value="1" @checked($type->is_active)> {{ __('infractions::infractions.fields.is_active') }}</label>
                        </td>
                        <td><button form="type-{{ $type->id }}" class="btn btn-default btn-sm"><i class="feather icon-save"></i> {{ __('ui.save') }}</button></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('infractions::infractions.add_type')">
            <form method="POST" action="{{ route('infractions.types.store') }}">
                @csrf
                <x-ui.form.input name="code" :label="__('infractions::infractions.fields.code')" required />
                <x-ui.form.input name="label" :label="__('infractions::infractions.fields.label')" required />
                <x-ui.form.input name="legal_basis" :label="__('infractions::infractions.fields.legal_basis')" />
                <x-ui.form.select name="severity" :label="__('infractions::infractions.fields.severity')" :options="$severities" />
                <x-ui.form.input name="potential_penalty" :label="__('infractions::infractions.fields.potential_penalty')" />
                <input type="hidden" name="is_active" value="1">
                <x-ui.form.actions :cancel="route('infractions.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
