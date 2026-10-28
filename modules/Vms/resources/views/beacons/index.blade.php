<x-layouts.app :title="__('vms::vms.beacons.title')">
    <x-ui.page-header :title="__('vms::vms.beacons.title')" :subtitle="__('vms::vms.beacons.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                @if ($missing->isNotEmpty())
                    <x-ui.card :title="__('vms::vms.beacons.missing_title')" :subtitle="__('vms::vms.beacons.missing_hint')" flush>
                        <x-ui.table>
                            <x-slot:head>
                                <th>{{ __('vms::vms.fields.vessel') }}</th>
                            </x-slot:head>
                            @foreach ($missing as $vessel)
                                <tr><td><x-ui.vessel-link :vessel="$vessel" /></td></tr>
                            @endforeach
                        </x-ui.table>
                    </x-ui.card>
                @endif

                <x-ui.card :title="__('vms::vms.beacons.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.beacons.serial_number') }}</th>
                            <th>{{ __('vms::vms.beacons.model') }}</th>
                            <th>{{ __('vms::vms.beacons.modem') }}</th>
                            <th>{{ __('vms::vms.beacons.assigned_to') }}</th>
                            <th>{{ __('vms::vms.beacons.started_on') }}</th>
                        </x-slot:head>
                        @forelse ($beacons as $beacon)
                            <tr>
                                <td>{{ $beacon->serial_number }}</td>
                                <td>{{ $beacon->model }}</td>
                                <td>{{ $beacon->modem ? __($beacon->modem->label()) : '—' }}</td>
                                <td>@if ($beacon->currentAssignment)<x-ui.vessel-link :vessel="$beacon->currentAssignment->vessel" />@else<span class="text-muted">{{ __('vms::vms.beacons.free') }}</span>@endif</td>
                                <td class="text-nowrap">{{ $beacon->currentAssignment?->started_on->isoFormat('L') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('vms::vms.beacons.obligations_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.fields.vessel') }}</th>
                            @foreach (['vms', 'ais', 'ers', 'eez_declaration', 'fishing_log'] as $field)
                                <th>{{ __('vms::vms.obligations.'.$field) }}</th>
                            @endforeach
                        </x-slot:head>
                        @forelse ($obligations as $obligation)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$obligation->vessel" /></td>
                                @foreach (['vms', 'ais', 'ers', 'eez_declaration', 'fishing_log'] as $field)
                                    @php($column = 'requires_'.$field)
                                    <td>{!! $obligation->$column ? '<i class="feather icon-check text-success"></i>' : '—' !!}</td>
                                @endforeach
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="6" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @can(App\Support\Authorization\Permission::VmsCreate->value)
                    <x-ui.card :title="__('vms::vms.beacons.assign_title')" :subtitle="__('vms::vms.beacons.assign_hint')">
                        <form method="POST" action="{{ route('vms.beacons.assign') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            <x-ui.form.select name="beacon_id" :label="__('vms::vms.beacons.serial_number')" :options="$beaconOptions" required />
                            <x-ui.form.input name="started_on" type="date" :label="__('vms::vms.beacons.started_on')" :value="today()->toDateString()" />
                            <x-ui.form.input name="replacement_reason" :label="__('vms::vms.beacons.replacement_reason')" />
                            <x-ui.form.actions :cancel="route('vms.beacons.index')" />
                        </form>
                    </x-ui.card>
                @endcan

                @can(App\Support\Authorization\Permission::VmsManage->value)
                    <x-ui.card :title="__('vms::vms.beacons.new_title')">
                        <form method="POST" action="{{ route('vms.beacons.store') }}">
                            @csrf
                            <x-ui.form.input name="serial_number" :label="__('vms::vms.beacons.serial_number')" required />
                            <x-ui.form.input name="model" :label="__('vms::vms.beacons.model')" />
                            <x-ui.form.input name="manufacturer" :label="__('vms::vms.beacons.manufacturer')" />
                            <x-ui.form.input name="supplier" :label="__('vms::vms.beacons.supplier')" />
                            <x-ui.form.select name="modem" :label="__('vms::vms.beacons.modem')" :options="$modems" />
                            <x-ui.form.input name="subscription" :label="__('vms::vms.beacons.subscription')" />
                            <x-ui.form.actions :cancel="route('vms.beacons.index')" />
                        </form>
                    </x-ui.card>

                    <x-ui.card :title="__('vms::vms.beacons.obligations_form')">
                        <form method="POST" action="{{ route('vms.obligations.store') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            @foreach (['vms', 'ais', 'ers', 'eez_declaration', 'fishing_log'] as $field)
                                <x-ui.form.checkbox :name="'requires_'.$field" :label="__('vms::vms.obligations.'.$field)" />
                            @endforeach
                            <x-ui.form.actions :cancel="route('vms.beacons.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
