<x-layouts.app :title="__('registry::registry.priorities.title')">
    <x-ui.page-header :title="__('registry::registry.priorities.title')" :subtitle="__('registry::registry.priorities.subtitle')" :breadcrumbs="[__('registry::registry.title') => route('registry.index')]" />

    <div class="page-body">
        <form method="POST" action="{{ route('registry.priorities.update') }}">
            @csrf
            @method('PUT')

            <x-ui.card :title="__('registry::registry.priorities.title')" flush>
                <x-ui.table>
                    <x-slot:head>
                        <th>{{ __('registry::registry.priorities.field') }}</th>
                        @foreach ($sources as $source)
                            <th>{{ __($source->label()) }}</th>
                        @endforeach
                    </x-slot:head>
                    @foreach ($fields as $field)
                        <tr>
                            <td>{{ __('registry::registry.priorities.fields.'.$field) }}</td>
                            @foreach ($sources as $source)
                                <td>
                                    <input type="number" min="1" max="99" name="ranks[{{ $field }}][{{ $source->value }}]" value="{{ old("ranks.{$field}.{$source->value}", $priorities->rank($field, $source)) }}" class="form-control form-control-sm">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>

            <x-ui.form.actions :cancel="route('registry.index')" />
        </form>
    </div>
</x-layouts.app>
