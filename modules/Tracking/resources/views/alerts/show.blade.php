<x-layouts.app :title="__('tracking::tracking.alerts.detail_title')">
    <x-ui.page-header :title="__('tracking::tracking.alerts.detail_title')" :breadcrumbs="[__('tracking::tracking.alerts.title') => route('tracking.alerts.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-5">
                <x-ui.card :title="__('tracking::tracking.alerts.summary')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('tracking::tracking.alerts.vessel')"><x-ui.vessel-link :vessel="$alert->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.type')">{{ __($alert->type->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.rule')">{{ $alert->rule?->code }}</x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.zone')">{{ $alert->zone?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.triggered_at')">{{ $alert->triggered_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.status')"><x-ui.status-badge :status="$alert->status" /></x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.acknowledged_by')">{{ $alert->acknowledger?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('tracking::tracking.alerts.operation')">{{ $alert->operation?->code }}</x-ui.detail>
                    </x-ui.detail-list>
                    @can(App\Support\Authorization\Permission::TrackingCreate->value)
                        @if ($alert->status === App\Enums\AlertStatus::Open)
                            <form method="POST" action="{{ route('tracking.alerts.acknowledge', $alert) }}" class="m-t-15">
                                @csrf
                                <button type="submit" class="btn btn-warning">{{ __('tracking::tracking.alerts.acknowledge') }}</button>
                            </form>
                        @endif
                    @endcan
                </x-ui.card>

                <x-ui.card :title="__('tracking::tracking.alerts.handlings')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.alerts.handled_at') }}</th>
                            <th>{{ __('tracking::tracking.alerts.handled_by') }}</th>
                            <th>{{ __('tracking::tracking.alerts.measures') }}</th>
                        </x-slot:head>
                        @forelse ($alert->handlings as $handling)
                            <tr>
                                <td class="text-nowrap">{{ $handling->handled_at?->isoFormat('L LT') }}</td>
                                <td>{{ $handling->handler?->name }}</td>
                                <td class="text-wrap text-pre-line">{{ $handling->measures }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-7">
                @can(App\Support\Authorization\Permission::TrackingCreate->value)
                    @if ($alert->status !== App\Enums\AlertStatus::Closed)
                        <x-ui.card :title="__('tracking::tracking.alerts.handle_title')" :subtitle="__('tracking::tracking.alerts.handle_hint')">
                            <form method="POST" action="{{ route('tracking.alerts.handle', $alert) }}">
                                @csrf
                                @foreach ($questions as $question)
                                    <x-ui.form.checkbox :name="'verification_questions['.$question.']'" :label="__('tracking::tracking.alerts.questions.'.$question)" />
                                @endforeach
                                <x-ui.form.checkbox name="is_vessel_contacted" :label="__('tracking::tracking.alerts.vessel_contacted')" />
                                <x-ui.form.textarea name="measures" :label="__('tracking::tracking.alerts.measures')" rows="3" />
                                <x-ui.form.select name="operation_id" :label="__('tracking::tracking.alerts.operation')" :options="$operations" :value="$alert->operation_id" />
                                <x-ui.form.checkbox name="close" :label="__('tracking::tracking.alerts.close_after')" />
                                <x-ui.form.actions :cancel="route('tracking.alerts.index')" />
                            </form>
                            @can(App\Support\Authorization\Permission::OperationsCreate->value)
                                <a href="{{ route('operations.create') }}" class="btn btn-outline-primary btn-mini m-t-10">{{ __('tracking::tracking.alerts.open_operation') }}</a>
                            @endcan
                        </x-ui.card>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
