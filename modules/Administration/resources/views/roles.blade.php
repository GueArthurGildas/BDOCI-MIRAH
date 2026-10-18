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
    </div>
</x-layouts.app>
