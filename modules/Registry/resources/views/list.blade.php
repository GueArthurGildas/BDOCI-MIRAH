<x-layouts.app :title="__('registry::registry.resources.'.$resource->key.'.title')">
    <x-ui.page-header :title="__('registry::registry.resources.'.$resource->key.'.title')" :breadcrumbs="[__('registry::registry.title') => route('registry.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('registry::registry.resources.'.$resource->key.'.title')" :subtitle="trans_choice('ui.results_count', $records->total())" flush>
            @can(App\Support\Authorization\Permission::RegistryManage->value)
                <x-slot:actions>
                    <a href="{{ route('registry.create', $resource->key) }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('registry::registry.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$records">
                <x-slot:head>
                    @foreach ($resource->columns as $column)
                        <th>{{ __('registry::registry.fields.'.$column) }}</th>
                    @endforeach
                    <th></th>
                </x-slot:head>

                @php($lookups = collect($resource->fields)->filter(fn ($field) => $field->type === 'select')->mapWithKeys(fn ($field) => [$field->name => $field->optionList()]))

                @forelse ($records as $record)
                    <tr>
                        @foreach ($resource->columns as $column)
                            @php($value = $record->{$column})
                            <td>
                                @if ($lookups->has($column))
                                    {{ $lookups[$column][$value instanceof BackedEnum ? $value->value : $value] ?? '—' }}
                                @elseif (is_bool($value))
                                    {{ $value ? __('ui.yes') : __('ui.no') }}
                                @elseif ($value instanceof \Carbon\CarbonInterface)
                                    {{ $value->isoFormat('L') }}
                                @else
                                    {{ $value ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::RegistryManage->value)
                                <a href="{{ route('registry.edit', [$resource->key, $record->getKey()]) }}" class="btn btn-default btn-sm">{{ __('ui.edit') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row :colspan="count($resource->columns) + 1" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
