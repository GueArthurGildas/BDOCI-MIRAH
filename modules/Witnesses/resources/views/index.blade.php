<x-layouts.app :title="__('witnesses::witnesses.title')">
    <x-ui.page-header :title="__('witnesses::witnesses.title')" :subtitle="__('witnesses::witnesses.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('witnesses.index')">
            <x-ui.filter-select name="status" :label="__('witnesses::witnesses.fields.status')" :options="App\Enums\WitnessReportStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="channel" :label="__('witnesses::witnesses.fields.channel')" :options="App\Enums\ReportChannel::options()" :value="$filters['channel'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('witnesses::witnesses.list_title')" :subtitle="trans_choice('ui.results_count', $reports->total())" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::WitnessesCreate->value)
                    <a href="{{ route('witnesses.create') }}" class="btn btn-primary btn-sm"><i class="feather icon-plus"></i> {{ __('witnesses::witnesses.record') }}</a>
                @endcan
            </x-slot:actions>
            <x-ui.table :paginator="$reports">
                <x-slot:head>
                    <th>#</th>
                    <th>{{ __('witnesses::witnesses.fields.received_at') }}</th>
                    <th>{{ __('witnesses::witnesses.fields.witness') }}</th>
                    <th>{{ __('witnesses::witnesses.fields.channel') }}</th>
                    <th>{{ __('witnesses::witnesses.fields.vessels') }}</th>
                    <th>{{ __('witnesses::witnesses.fields.status') }}</th>
                </x-slot:head>
                @forelse ($reports as $report)
                    <tr>
                        <td><a href="{{ route('witnesses.show', $report) }}" class="f-w-600">{{ $report->id }}</a></td>
                        <td class="text-nowrap">{{ $report->received_at->isoFormat('L LT') }}</td>
                        <td>{{ $report->witness?->name ?? '—' }}</td>
                        <td>{{ __($report->channel->label()) }}</td>
                        <td>{{ $report->reportedVessels->pluck('name')->filter()->join(', ') ?: '—' }}</td>
                        <td><x-ui.status-badge :status="$report->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
