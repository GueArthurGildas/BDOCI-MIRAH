<x-layouts.app :title="__('administration::administration.roles.title')">
    <x-ui.page-header :title="__('administration::administration.roles.title')" :subtitle="__('administration::administration.roles.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.roles.title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::administration.roles.permission') }}</th>
                    @foreach ($roles as $role)
                        <th class="text-center">{{ __($role->label()) }}</th>
                    @endforeach
                </x-slot:head>
                @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ __('administration::administration.permissions.'.$permission->value) }}</td>
                        @foreach ($roles as $role)
                            <td class="text-center">
                                @if (in_array($permission, $role->permissions(), true))
                                    <i class="feather icon-check text-success f-18" aria-label="{{ __('ui.yes') }}"></i>
                                @else
                                    <i class="feather icon-minus text-muted" aria-label="{{ __('ui.no') }}"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('administration::administration.roles.groups_title')" :subtitle="__('administration::administration.roles.groups_subtitle')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::administration.roles.group') }}</th>
                    <th>{{ __('administration::administration.roles.permission') }}</th>
                </x-slot:head>
                @foreach ($groups as $group)
                    <tr>
                        <td class="f-w-600">{{ __($group->label()) }}</td>
                        <td>
                            @forelse ($group->permissions() as $permission)
                                <span class="label label-inverse-info">{{ __('administration::administration.permissions.'.$permission->value) }}</span>
                            @empty
                                <span class="text-muted">{{ __('administration::administration.roles.group_pending') }}</span>
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
