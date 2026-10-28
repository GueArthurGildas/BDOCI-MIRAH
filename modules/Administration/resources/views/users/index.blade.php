<x-layouts.app :title="__('administration::administration.users.title')">
    <x-ui.page-header :title="__('administration::administration.users.title')" :subtitle="__('administration::administration.users.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('administration.users.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$search" :placeholder="__('administration::administration.users.search_placeholder')" class="col-md-5" />
        </x-ui.filters>

        <x-ui.card :title="__('administration::administration.users.list_title')" :subtitle="trans_choice('ui.results_count', $users->total())" flush>
            <x-slot:actions>
                <a href="{{ route('administration.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-plus"></i> {{ __('administration::administration.users.new') }}
                </a>
            </x-slot:actions>

            <x-ui.table :paginator="$users">
                <x-slot:head>
                    <th>{{ __('administration::administration.users.fields.name') }}</th>
                    <th>{{ __('administration::administration.users.fields.team') }}</th>
                    <th>{{ __('administration::administration.users.fields.role') }}</th>
                    <th>{{ __('administration::administration.users.fields.status') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <span class="f-w-600">{{ $user->name }}</span>
                            <div class="text-muted f-12">{{ $user->email }}</div>
                        </td>
                        <td>{{ $user->team?->name ?? '—' }}</td>
                        <td>
                            @foreach ($user->roles as $role)
                                @if ($case = App\Support\Authorization\Role::tryFrom($role->name))
                                    {{ __($case->label()) }}
                                @else
                                    <span class="label label-inverse-info">{{ __(App\Support\Authorization\PermissionGroup::from(substr($role->name, strlen(App\Support\Authorization\PermissionGroup::ROLE_PREFIX)))->label()) }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            <span @class(['label', 'label-success' => $user->is_active, 'label-default' => ! $user->is_active])>
                                {{ $user->is_active ? __('administration::administration.users.active') : __('administration::administration.users.inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('administration.users.edit', $user) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
