<x-layouts.app :title="__('administration::custom_fields.title')">
    <x-ui.page-header :title="__('administration::custom_fields.title')" :subtitle="__('administration::custom_fields.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::custom_fields.list_title')" flush>
            <x-slot:actions>
                <a href="{{ route('administration.custom-fields.create') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-plus"></i> {{ __('administration::custom_fields.new') }}
                </a>
            </x-slot:actions>

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::custom_fields.label') }}</th>
                    <th>{{ __('administration::custom_fields.code') }}</th>
                    <th>{{ __('administration::custom_fields.object') }}</th>
                    <th>{{ __('administration::custom_fields.type') }}</th>
                    <th>{{ __('administration::custom_fields.visible') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($fields as $field)
                    <tr>
                        <td class="f-w-600">{{ $field->label }}</td>
                        <td><code>{{ $field->code }}</code></td>
                        <td>{{ __('administration::custom_fields.objects.'.(config('custom-fields.objects')[$field->object_type] ?? 'unknown')) }}</td>
                        <td>{{ $field->type->label() }}</td>
                        <td>{{ __($field->is_visible ? 'ui.yes' : 'ui.no') }}</td>
                        <td class="text-end">
                            <a href="{{ route('administration.custom-fields.edit', $field) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
