<x-ui.card :title="$data->widget->title()" flush data-guide="observations">
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('dashboard::dashboard.columns.date') }}</th>
            <th>{{ __('dashboard::dashboard.columns.level') }}</th>
            <th>{{ __('dashboard::dashboard.columns.vessel') }}</th>
            <th>{{ __('dashboard::dashboard.columns.description') }}</th>
        </x-slot:head>
        @forelse ($data->records as $observation)
            <tr>
                <td class="text-nowrap">{{ $observation->observed_at->isoFormat('L LT') }}</td>
                <td><x-ui.status-badge :status="$observation->level" /></td>
                <td>
                    @if ($observation->vessel)
                        <x-ui.vessel-link :vessel="$observation->vessel" />
                    @endif
                </td>
                <td class="text-wrap">{{ Str::limit($observation->description, 120) }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
