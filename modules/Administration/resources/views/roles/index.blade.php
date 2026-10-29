<x-layouts.app :title="__('administration::administration.roles.title')">
    <x-ui.page-header :title="__('administration::administration.roles.title')" :subtitle="__('administration::administration.roles.subtitle')" />

    <div class="page-body">
        <x-ui.card data-guide="list" :title="__('administration::administration.roles.list_title')" flush>
            <x-slot:actions>
                <a href="{{ route('administration.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-plus"></i> {{ __('administration::administration.roles.new') }}
                </a>
            </x-slot:actions>

            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::administration.roles.name') }}</th>
                    <th>{{ __('administration::administration.roles.description') }}</th>
                    <th>{{ __('administration::administration.roles.type') }}</th>
                    <th>{{ __('administration::administration.roles.inherits_from') }}</th>
                    <th class="text-end">{{ __('administration::administration.roles.users_count') }}</th>
                    <th class="text-end">{{ __('administration::administration.roles.permissions_count') }}</th>
                    <th></th>
                </x-slot:head>
                @foreach ($roles as $role)
                    @php($isSystem = App\Support\Authorization\Role::isSystem($role->name))
                    <tr>
                        <td class="f-w-600">{{ App\Support\Authorization\Role::labelFor($role->name) }}</td>
                        <td class="gs-cell-wrap text-muted f-12">{{ App\Support\Authorization\Role::descriptionFor($role) }}</td>
                        <td>
                            <span @class(['label', 'label-inverse' => $isSystem, 'label-default' => ! $isSystem])>
                                {{ __($isSystem ? 'administration::administration.roles.system' : 'administration::administration.roles.custom') }}
                            </span>
                        </td>
                        <td>{{ $role->parent ? App\Support\Authorization\Role::labelFor($role->parent->name) : '—' }}</td>
                        <td class="text-end">{{ $role->users_count }}</td>
                        <td class="text-end">{{ count($effective[$role->id]) }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('administration.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">{{ __('ui.edit') }}</a>
                            @unless ($isSystem)
                                <form method="POST" action="{{ route('administration.roles.destroy', $role) }}" class="d-inline" data-confirm="{{ __('administration::administration.roles.confirm_delete', ['role' => $role->name]) }}" data-confirm-label="{{ __('ui.confirm.delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($role->users_count > 0) title="{{ $role->users_count > 0 ? __('administration::administration.roles.cannot_delete_in_use') : '' }}">
                                        {{ __('administration::administration.roles.delete') }}
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        <x-ui.card data-guide="matrix" :title="__('administration::administration.roles.matrix_title')" :subtitle="__('administration::administration.roles.matrix_hint')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('administration::administration.roles.permission') }}</th>
                    @foreach ($roles as $role)
                        <th class="text-center">{{ App\Support\Authorization\Role::labelFor($role->name) }}</th>
                    @endforeach
                </x-slot:head>
                @foreach ($permissions as $permission)
                    <tr>
                        <td>{{ __('administration::administration.permissions.'.$permission->value) }}</td>
                        @foreach ($roles as $role)
                            <td class="text-center">
                                @if (in_array($permission->value, $effective[$role->id], true))
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
