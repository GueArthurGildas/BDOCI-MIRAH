<x-layouts.app :title="__('vms::vms.failures.detail_title')">
    <x-ui.page-header :title="__('vms::vms.failures.detail_title')" :breadcrumbs="[__('vms::vms.failures.title') => route('vms.failures.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-5">
                <x-ui.card :title="__('vms::vms.failures.summary')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('vms::vms.fields.vessel')"><x-ui.vessel-link :vessel="$failure->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.category')">{{ __($failure->category->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.started_at')">{{ $failure->started_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.report_frequency')">{{ __('vms::vms.failures.every_hours', ['hours' => $failure->report_frequency_h]) }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.deadline')">{{ $failure->repairDeadlineAt()->isoFormat('L') }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.ended_at')">{{ $failure->ended_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.operation')">{{ $failure->operation?->code }}</x-ui.detail>
                        <x-ui.detail :label="__('vms::vms.failures.cessation_order')">
                            @if ($failure->cessationOrder)
                                {{ $failure->cessationOrder->ordered_at->isoFormat('L LT') }}
                                @if ($failure->cessationOrder->includes_suspension) · {{ __('vms::vms.failures.includes_suspension') }} @endif
                                @if ($failure->cessationOrder->requires_port_return) · {{ __('vms::vms.failures.requires_port_return') }} @endif
                            @endif
                        </x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                @if ($failure->ended_at === null)
                    @can(App\Support\Authorization\Permission::VmsCreate->value)
                        <x-ui.card :title="__('vms::vms.failures.attest')">
                            <form method="POST" action="{{ route('vms.failures.attest', $failure) }}">
                                @csrf
                                <x-ui.form.input name="attested_on" type="date" :label="__('vms::vms.failures.attested_on')" :value="today()->toDateString()" />
                                <x-ui.form.actions :cancel="route('vms.failures.show', $failure)" />
                            </form>
                        </x-ui.card>
                    @endcan
                    @can(App\Support\Authorization\Permission::VmsManage->value)
                        <x-ui.card :title="__('vms::vms.failures.order')">
                            <form method="POST" action="{{ route('vms.failures.order', $failure) }}">
                                @csrf
                                <x-ui.form.checkbox name="includes_suspension" :label="__('vms::vms.failures.includes_suspension')" />
                                <x-ui.form.checkbox name="requires_port_return" :label="__('vms::vms.failures.requires_port_return')" />
                                <x-ui.form.actions :cancel="route('vms.failures.show', $failure)" />
                            </form>
                        </x-ui.card>
                    @endcan
                @endif
            </div>

            <div class="col-xl-7">
                <x-ui.card :title="__('vms::vms.failures.reports_title')" :subtitle="__('vms::vms.failures.reports_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.failures.received_at') }}</th>
                            <th>{{ __('vms::vms.failures.latitude') }}</th>
                            <th>{{ __('vms::vms.failures.longitude') }}</th>
                            <th>{{ __('vms::vms.failures.entered_by') }}</th>
                        </x-slot:head>
                        @forelse ($failure->manualReports->sortByDesc('received_at') as $report)
                            <tr>
                                <td class="text-nowrap">{{ $report->received_at->isoFormat('L LT') }}</td>
                                <td>{{ $report->latitude }}</td>
                                <td>{{ $report->longitude }}</td>
                                <td>{{ $report->enteredBy?->name }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                @if ($failure->ended_at === null)
                    @can(App\Support\Authorization\Permission::VmsCreate->value)
                        <x-ui.card :title="__('vms::vms.failures.new_report')">
                            <form method="POST" action="{{ route('vms.failures.report', $failure) }}">
                                @csrf
                                <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('vms::vms.failures.latitude')" required />
                                <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('vms::vms.failures.longitude')" required />
                                <x-ui.form.input name="received_at" type="datetime-local" :label="__('vms::vms.failures.received_at')" />
                                <x-ui.form.actions :cancel="route('vms.failures.show', $failure)" />
                            </form>
                        </x-ui.card>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
