@php($title = $dpep->number ?? $dpep->external_id)

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :subtitle="__('mrep::mrep.dpeps.title')" :breadcrumbs="[__('mrep::mrep.dpeps.title') => route('mrep.dpeps.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('mrep::mrep.dpeps.title')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('mrep::mrep.fields.vessel')">
                            @if ($dpep->vessel)
                                <x-ui.vessel-link :vessel="$dpep->vessel" />
                            @else
                                <span class="label label-warning">{{ __('mrep::mrep.unmatched') }}</span>
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.port')">{{ $dpep->port?->name ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.planned_arrival_at')">{{ $dpep->planned_arrival_at?->isoFormat('L LT') ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.purpose')">{{ $dpep->purpose ? __($dpep->purpose->label()) : '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.last_port_call')">{{ $dpep->last_port_call ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.is_complete')">{{ $dpep->is_complete === null ? '—' : __($dpep->is_complete ? 'ui.yes' : 'ui.no') }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.deadline_72h')">{{ $dpep->is_72h_deadline_met === null ? '—' : __($dpep->is_72h_deadline_met ? 'ui.yes' : 'ui.no') }}</x-ui.detail>
                        <x-ui.detail :label="__('mrep::mrep.fields.read_at')">{{ $dpep->read_at->isoFormat('L LT') }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                @foreach (['catches_on_board' => $dpep->catches_on_board, 'catches_to_land' => $dpep->catches_to_land] as $key => $catches)
                    <x-ui.card :title="__('mrep::mrep.fields.'.$key)" flush>
                        <x-ui.table>
                            <x-slot:head>
                                <th>{{ __('mrep::mrep.fields.species') }}</th>
                                <th class="text-end">{{ __('mrep::mrep.fields.weight_kg') }}</th>
                            </x-slot:head>
                            @forelse ($catches ?? [] as $line)
                                <tr>
                                    <td>{{ $species[$line['species'] ?? ''] ?? ($line['species'] ?? '—') }}</td>
                                    <td class="text-end">{{ Number::format($line['weight_kg'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <x-ui.empty-row colspan="2" />
                            @endforelse
                        </x-ui.table>
                    </x-ui.card>
                @endforeach
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('mrep::mrep.decisions_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('mrep::mrep.fields.decision') }}</th>
                            <th>{{ __('mrep::mrep.fields.decided_at') }}</th>
                            <th>{{ __('mrep::mrep.fields.decision_maker') }}</th>
                        </x-slot:head>
                        @forelse ($dpep->entryDecisions as $decision)
                            <tr>
                                <td><x-ui.status-badge :status="$decision->decision" /></td>
                                <td>{{ $decision->decided_at->isoFormat('L LT') }}</td>
                                <td>{{ $decision->decision_maker ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('mrep::mrep.port_calls_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('mrep::mrep.fields.arrival') }}</th>
                            <th>{{ __('mrep::mrep.fields.departure') }}</th>
                            <th>{{ __('mrep::mrep.fields.is_inspected') }}</th>
                            <th>{{ __('mrep::mrep.fields.is_infraction_recorded') }}</th>
                        </x-slot:head>
                        @forelse ($dpep->portCalls as $call)
                            <tr>
                                <td>{{ $call->arrival?->isoFormat('L LT') ?? '—' }}</td>
                                <td>{{ $call->departure?->isoFormat('L LT') ?? '—' }}</td>
                                <td>
                                    @if ($call->inspection)
                                        <a href="{{ route('inspections.show', $call->inspection) }}">{{ $call->inspection->reference ?? $call->inspection->external_id }}</a>
                                    @else
                                        {{ $call->is_inspected === null ? '—' : __($call->is_inspected ? 'ui.yes' : 'ui.no') }}
                                    @endif
                                </td>
                                <td>{{ $call->is_infraction_recorded === null ? '—' : __($call->is_infraction_recorded ? 'ui.yes' : 'ui.no') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('mrep::mrep.notifications_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('mrep::mrep.fields.recipient') }}</th>
                            <th>{{ __('mrep::mrep.fields.channel') }}</th>
                            <th>{{ __('mrep::mrep.fields.notified_at') }}</th>
                            <th>{{ __('mrep::mrep.fields.status') }}</th>
                        </x-slot:head>
                        @forelse ($dpep->notifications as $notification)
                            <tr>
                                <td class="text-wrap">{{ $notification->recipient ?? '—' }}</td>
                                <td>{{ $notification->channel ?? '—' }}</td>
                                <td>{{ $notification->notified_at?->isoFormat('L LT') ?? '—' }}</td>
                                <td>{{ $notification->status ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
