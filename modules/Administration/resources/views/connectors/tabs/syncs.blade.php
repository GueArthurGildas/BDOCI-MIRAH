<x-ui.card :title="__('administration::administration.connectors.tabs.syncs')" :subtitle="__('administration::administration.connectors.limit_hint', ['count' => $limit])" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('administration::administration.connectors.columns.started_at') }}</th>
            <th>{{ __('administration::administration.connectors.columns.duration') }}</th>
            <th class="text-end">{{ __('administration::administration.connectors.columns.received') }}</th>
            <th class="text-end">{{ __('administration::administration.connectors.columns.rejected') }}</th>
            <th>{{ __('administration::administration.connectors.columns.message') }}</th>
        </x-slot:head>
        @forelse ($syncs as $sync)
            <tr>
                <td>{{ $sync->started_at->isoFormat('L LT') }}</td>
                <td>
                    @if ($sync->finished_at)
                        {{ $sync->started_at->diffForHumans($sync->finished_at, Carbon\CarbonInterface::DIFF_ABSOLUTE, true) }}
                    @else
                        <span class="label label-danger">{{ __('administration::administration.connectors.interrupted') }}</span>
                    @endif
                </td>
                <td class="text-end">{{ $sync->received_count }}</td>
                <td class="text-end">{{ $sync->rejected_count }}</td>
                <td class="f-12">{{ $sync->message ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row :colspan="5">{{ __('administration::administration.connectors.no_syncs') }}</x-ui.empty-row>
        @endforelse
    </x-ui.table>
</x-ui.card>
