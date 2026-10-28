<x-ui.card :title="__('vessels::vessels.tabs.infractions')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.reference') }}</th>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.infraction_type') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
        </x-slot:head>
        @forelse ($vessel->infractionCases as $case)
            <tr>
                <td>
                    @can(App\Support\Authorization\Permission::InfractionsView->value)
                        <a href="{{ route('infractions.show', $case) }}">{{ $case->reference }}</a>
                    @else
                        {{ $case->reference }}
                    @endcan
                </td>
                <td>{{ $case->opened_at->isoFormat('L') }}</td>
                <td class="text-wrap">{{ $case->upheld->map(fn ($upheld) => $upheld->type->label)->implode(', ') ?: '—' }}</td>
                <td><x-ui.status-badge :status="$case->status" /></td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
