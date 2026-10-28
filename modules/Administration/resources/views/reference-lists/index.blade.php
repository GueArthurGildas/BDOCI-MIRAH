<x-layouts.app :title="__('administration::reference_lists.title')">
    <x-ui.page-header :title="__('administration::reference_lists.title')" :subtitle="__('administration::reference_lists.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::reference_lists.list_title')" flush>
            <x-slot:actions>
                <a href="{{ route('administration.reference-lists.create') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-plus"></i> {{ __('administration::reference_lists.new') }}
                </a>
            </x-slot:actions>

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::reference_lists.label') }}</th>
                    <th>{{ __('administration::reference_lists.code') }}</th>
                    <th class="text-end">{{ __('administration::reference_lists.values_count') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($lists as $list)
                    <tr>
                        <td class="f-w-600">{{ $list->label }}</td>
                        <td><code>{{ $list->code }}</code></td>
                        <td class="text-end">{{ $list->values_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('administration.reference-lists.edit', $list) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
