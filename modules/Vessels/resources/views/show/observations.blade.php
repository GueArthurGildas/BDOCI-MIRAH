<x-ui.card :title="__('vessels::vessels.tabs.observations')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.date') }}</th>
            <th>{{ __('vessels::vessels.columns.level') }}</th>
            <th>{{ __('vessels::vessels.columns.description') }}</th>
            <th>{{ __('vessels::vessels.columns.author') }}</th>
            <th>{{ __('vessels::vessels.columns.status') }}</th>
            <th>{{ __('vessels::vessels.tabs.logbook') }}</th>
        </x-slot:head>
        @forelse ($vessel->observations as $observation)
            <tr id="item-observations-{{ $observation->id }}">
                <td class="text-nowrap">
                    @can(App\Support\Authorization\Permission::LogbookView->value)
                        <a href="{{ route('logbook.show', $observation) }}">{{ $observation->observed_at->isoFormat('L LT') }}</a>
                    @else
                        {{ $observation->observed_at->isoFormat('L LT') }}
                    @endcan
                </td>
                <td><x-ui.status-badge :status="$observation->level" /></td>
                <td class="text-wrap">{{ $observation->description }}</td>
                <td>{{ $observation->author->name }}</td>
                <td><x-ui.status-badge :status="$observation->status" /></td>
                <td class="text-nowrap">
                    @if ($observation->logbookEntry)
                        @can(App\Support\Authorization\Permission::LogbookView->value)
                            <a href="{{ route('logbook.days.show', $observation->logbookEntry->logbook_day_id) }}#entry-{{ $observation->logbookEntry->id }}">{{ $observation->logbookEntry->occurred_at->isoFormat('L LT') }}</a>
                        @else
                            {{ $observation->logbookEntry->occurred_at->isoFormat('L LT') }}
                        @endcan
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <x-ui.empty-row colspan="6" />
        @endforelse
    </x-ui.table>
</x-ui.card>

@can(App\Support\Authorization\Permission::WitnessesView->value)
    <x-ui.card :title="__('vessels::vessels.logbook.witness_reports')" flush>
        <x-ui.table>
            <x-slot:head>
                <th>{{ __('vessels::vessels.columns.date') }}</th>
                <th>{{ __('vessels::vessels.columns.source') }}</th>
                <th>{{ __('vessels::vessels.columns.status') }}</th>
                <th>{{ __('vessels::vessels.tabs.logbook') }}</th>
            </x-slot:head>
            @forelse ($vessel->witnessReports as $report)
                <tr id="item-observations-report-{{ $report->id }}">
                    <td class="text-nowrap"><a href="{{ route('witnesses.show', $report) }}">{{ __('vessels::vessels.logbook.witness_report', ['id' => $report->id]) }}</a><div class="text-muted f-12">{{ $report->received_at->isoFormat('L LT') }} UTC</div></td>
                    <td>{{ __($report->channel->label()) }}</td>
                    <td><x-ui.status-badge :status="$report->status" /></td>
                    <td class="text-nowrap">
                        @if ($report->logbookEntry)
                            <a href="{{ route('logbook.days.show', $report->logbookEntry->logbook_day_id) }}#entry-{{ $report->logbookEntry->id }}">{{ $report->logbookEntry->occurred_at->isoFormat('L LT') }}</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="4" />
            @endforelse
        </x-ui.table>
    </x-ui.card>
@endcan
