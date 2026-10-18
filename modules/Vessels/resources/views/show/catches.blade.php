<x-ui.card :title="__('vessels::vessels.tabs.catches')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.period') }}</th>
            <th>{{ __('vessels::vessels.columns.species') }}</th>
            <th class="text-end">{{ __('vessels::vessels.columns.quantity') }}</th>
            <th>{{ __('vessels::vessels.columns.source') }}</th>
        </x-slot:head>
        @forelse ($vessel->catchDeclarations as $declaration)
            <tr>
                <td>{{ $declaration->period_start->isoFormat('L') }} – {{ $declaration->period_end->isoFormat('L') }}</td>
                <td>{{ $declaration->species->name }} <span class="text-muted f-12">({{ $declaration->species->fao_code }})</span></td>
                <td class="text-end">{{ __('vessels::vessels.units.kilograms', ['value' => Number::format($declaration->quantity_kg)]) }}</td>
                <td>{{ __($declaration->source->label()) }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
