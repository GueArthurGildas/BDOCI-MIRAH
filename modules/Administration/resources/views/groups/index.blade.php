<x-layouts.app :title="__('administration::groups.title')">
    <x-ui.page-header :title="__('administration::groups.title')" :subtitle="__('administration::groups.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::groups.list_title')" flush>
            <x-slot:actions>
                <a href="{{ route('administration.groups.create') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-plus"></i> {{ __('administration::groups.new') }}
                </a>
            </x-slot:actions>

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::groups.name') }}</th>
                    <th>{{ __('administration::groups.description') }}</th>
                    <th>{{ __('administration::administration.roles.type') }}</th>
                    <th class="text-end">{{ __('administration::groups.members_count') }}</th>
                    <th class="text-end">{{ __('administration::groups.permissions_count') }}</th>
                    <th></th>
                </x-slot:head>
                @foreach ($groups as $group)
                    <tr>
                        <td class="f-w-600">{{ $group->displayLabel() }}</td>
                        <td class="gs-cell-wrap text-muted f-12">{{ $group->description() }}</td>
                        <td>
                            <span @class(['label', 'label-inverse' => $group->is_system, 'label-default' => ! $group->is_system])>
                                {{ __($group->is_system ? 'administration::administration.roles.system' : 'administration::administration.roles.custom') }}
                            </span>
                        </td>
                        <td class="text-end">{{ $group->members_count }}</td>
                        <td class="text-end">{{ $group->grants_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('administration.groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                            @unless ($group->is_system)
                                <form method="POST" action="{{ route('administration.groups.destroy', $group) }}" class="d-inline" data-confirm="{{ __('administration::groups.confirm_delete', ['group' => $group->label]) }}" data-confirm-label="{{ __('ui.confirm.delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('administration::administration.roles.delete') }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
