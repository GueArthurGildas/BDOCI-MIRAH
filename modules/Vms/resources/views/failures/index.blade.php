<x-layouts.app :title="__('vms::vms.failures.title')">
    <x-ui.page-header :title="__('vms::vms.failures.title')" :subtitle="__('vms::vms.failures.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('vms::vms.failures.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.fields.vessel') }}</th>
                            <th>{{ __('vms::vms.failures.category') }}</th>
                            <th>{{ __('vms::vms.failures.started_at') }}</th>
                            <th>{{ __('vms::vms.failures.deadline') }}</th>
                            <th>{{ __('vms::vms.failures.state') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($failures as $failure)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$failure->vessel" /></td>
                                <td>{{ __($failure->category->label()) }}</td>
                                <td class="text-nowrap">{{ $failure->started_at->isoFormat('L LT') }}</td>
                                <td class="text-nowrap">{{ $failure->repairDeadlineAt()->isoFormat('L') }}</td>
                                <td>
                                    @if ($failure->ended_at)
                                        <span class="label label-success">{{ __('vms::vms.failures.repaired') }}</span>
                                    @elseif ($failure->isOverdue())
                                        <span class="label label-danger">{{ __('vms::vms.failures.overdue') }}</span>
                                    @else
                                        <span class="label label-warning">{{ __('vms::vms.failures.ongoing') }}</span>
                                    @endif
                                </td>
                                <td class="text-end"><a href="{{ route('vms.failures.show', $failure) }}" class="btn btn-outline-primary btn-mini">{{ __('vms::vms.failures.open') }}</a></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="6" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('vms::vms.failures.cutoffs_title')" :subtitle="__('vms::vms.failures.cutoffs_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.fields.vessel') }}</th>
                            <th>{{ __('vms::vms.failures.cutoff_period') }}</th>
                            <th>{{ __('vms::vms.failures.reason') }}</th>
                        </x-slot:head>
                        @forelse ($cutoffs as $cutoff)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$cutoff->vessel" /></td>
                                <td class="text-nowrap">{{ $cutoff->started_at->isoFormat('L LT') }} → {{ $cutoff->ended_at->isoFormat('L LT') }}</td>
                                <td>{{ $cutoff->reason }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @can(App\Support\Authorization\Permission::VmsCreate->value)
                    <x-ui.card :title="__('vms::vms.failures.declare')">
                        <form method="POST" action="{{ route('vms.failures.store') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            <x-ui.form.select name="category" :label="__('vms::vms.failures.category')" :options="$categories" required />
                            <x-ui.form.input name="started_at" type="datetime-local" :label="__('vms::vms.failures.started_at')" />
                            <x-ui.form.select name="operation_id" :label="__('vms::vms.failures.operation')" :options="$operations" />
                            <x-ui.form.actions :cancel="route('vms.failures.index')" />
                        </form>
                    </x-ui.card>
                @endcan

                @can(App\Support\Authorization\Permission::VmsManage->value)
                    <x-ui.card :title="__('vms::vms.failures.authorize_cutoff')">
                        <form method="POST" action="{{ route('vms.cutoffs.store') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            <x-ui.form.input name="started_at" type="datetime-local" :label="__('vms::vms.failures.cutoff_start')" required />
                            <x-ui.form.input name="ended_at" type="datetime-local" :label="__('vms::vms.failures.cutoff_end')" required />
                            <x-ui.form.input name="reason" :label="__('vms::vms.failures.reason')" />
                            <x-ui.form.actions :cancel="route('vms.failures.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
