<x-layouts.app :title="__('mrep::mrep.dpeps.title')">
    <x-ui.page-header :title="__('mrep::mrep.dpeps.title')" :subtitle="__('mrep::mrep.dpeps.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('mrep.dpeps.index')">
            <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('mrep::mrep.dpeps.search_placeholder')" class="col-md-3" />
            <x-ui.filter-select name="purpose" :label="__('mrep::mrep.fields.purpose')" :options="App\Enums\MrepPurpose::options()" :value="$filters['purpose'] ?? null" />
            <x-ui.filter-select name="decision" :label="__('mrep::mrep.fields.decision')" :options="App\Enums\EntryDecision::options()" :value="$filters['decision'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('mrep::mrep.dpeps.list_title')" :subtitle="trans_choice('ui.results_count', $dpeps->total())" flush>
            <x-ui.table :paginator="$dpeps">
                <x-slot:head>
                    <th>{{ __('mrep::mrep.fields.number') }}</th>
                    <th>{{ __('mrep::mrep.fields.vessel') }}</th>
                    <th>{{ __('mrep::mrep.fields.port') }}</th>
                    <th>{{ __('mrep::mrep.fields.planned_arrival_at') }}</th>
                    <th>{{ __('mrep::mrep.fields.purpose') }}</th>
                    <th>{{ __('mrep::mrep.fields.decision') }}</th>
                    <th>{{ __('mrep::mrep.fields.deadline_72h') }}</th>
                    <th>{{ __('mrep::mrep.fields.read_at') }}</th>
                </x-slot:head>
                @forelse ($dpeps as $dpep)
                    <tr>
                        <td><a href="{{ route('mrep.dpeps.show', $dpep) }}" class="f-w-600">{{ $dpep->number ?? $dpep->external_id }}</a></td>
                        <td>
                            @if ($dpep->vessel)
                                <x-ui.vessel-link :vessel="$dpep->vessel" />
                            @else
                                <span class="label label-warning">{{ __('mrep::mrep.unmatched') }}</span>
                            @endif
                        </td>
                        <td>{{ $dpep->port?->name ?? '—' }}</td>
                        <td>{{ $dpep->planned_arrival_at?->isoFormat('L LT') ?? '—' }}</td>
                        <td>{{ $dpep->purpose ? __($dpep->purpose->label()) : '—' }}</td>
                        <td>
                            @if ($dpep->latestDecision)
                                <x-ui.status-badge :status="$dpep->latestDecision->decision" />
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $dpep->is_72h_deadline_met === null ? '—' : __($dpep->is_72h_deadline_met ? 'ui.yes' : 'ui.no') }}</td>
                        <td>{{ $dpep->read_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="8" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
