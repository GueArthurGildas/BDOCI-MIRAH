<x-layouts.app :title="__('vms::vms.requests.title')">
    <x-ui.page-header :title="__('vms::vms.requests.title')" :subtitle="__('vms::vms.requests.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('vms::vms.requests.assessments_title')" :subtitle="__('vms::vms.requests.assessments_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.fields.vessel') }}</th>
                            <th>{{ __('vms::vms.requests.requested_on') }}</th>
                            <th>{{ __('vms::vms.requests.status') }}</th>
                            <th>{{ __('vms::vms.requests.result') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($assessmentRequests as $request)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$request->vessel" /></td>
                                <td class="text-nowrap">{{ $request->requested_on->isoFormat('L') }}</td>
                                <td><x-ui.status-badge :status="$request->status" /></td>
                                <td>@if ($request->assessments->last())<x-ui.status-badge :status="$request->assessments->last()->result" />@endif</td>
                                <td>
                                    @can(App\Support\Authorization\Permission::VmsCreate->value)
                                        <form method="POST" action="{{ route('vms.requests.assess', $request) }}" class="d-flex gap-1">
                                            @csrf
                                            <select name="result" class="form-select form-select-sm" aria-label="{{ __('vms::vms.requests.result') }}">
                                                @foreach ($results as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-mini">{{ __('vms::vms.requests.assess') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('vms::vms.requests.new_beacons_title')" :subtitle="__('vms::vms.requests.new_beacons_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.fields.vessel') }}</th>
                            <th>{{ __('vms::vms.requests.requested_on') }}</th>
                            <th>{{ __('vms::vms.requests.status') }}</th>
                            <th>{{ __('vms::vms.requests.installer') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($newBeaconRequests as $request)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$request->vessel" /></td>
                                <td class="text-nowrap">{{ $request->requested_on->isoFormat('L') }}</td>
                                <td><x-ui.status-badge :status="$request->status" /></td>
                                <td>{{ $request->installations->last()?->installer->legalEntity->name }}</td>
                                <td>
                                    @can(App\Support\Authorization\Permission::VmsCreate->value)
                                        @if ($request->status === App\Enums\BeaconRequestStatus::Requested)
                                            <form method="POST" action="{{ route('vms.requests.install', $request) }}" class="d-flex gap-1">
                                                @csrf
                                                <select name="installer_id" class="form-select form-select-sm" required aria-label="{{ __('vms::vms.requests.installer') }}">
                                                    @foreach ($installerOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="beacon_id" class="form-select form-select-sm" aria-label="{{ __('vms::vms.beacons.serial_number') }}">
                                                    <option value="">—</option>
                                                    @foreach ($beaconOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-mini">{{ __('vms::vms.requests.install') }}</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                <x-ui.card :title="__('vms::vms.requests.installers_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('vms::vms.requests.installer') }}</th>
                            <th>{{ __('vms::vms.requests.approved_until') }}</th>
                        </x-slot:head>
                        @forelse ($installers as $installer)
                            <tr><td>{{ $installer->legalEntity->name }}</td><td class="text-nowrap">{{ $installer->approved_until?->isoFormat('L') }}</td></tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                @can(App\Support\Authorization\Permission::VmsCreate->value)
                    <x-ui.card :title="__('vms::vms.requests.new_assessment')">
                        <form method="POST" action="{{ route('vms.requests.assessment') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            <x-ui.form.actions :cancel="route('vms.requests.index')" />
                        </form>
                    </x-ui.card>

                    <x-ui.card :title="__('vms::vms.requests.new_beacon')">
                        <form method="POST" action="{{ route('vms.requests.new-beacon') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                            <x-ui.form.actions :cancel="route('vms.requests.index')" />
                        </form>
                    </x-ui.card>
                @endcan

                @can(App\Support\Authorization\Permission::VmsManage->value)
                    <x-ui.card :title="__('vms::vms.requests.new_installer')">
                        <form method="POST" action="{{ route('vms.requests.installers.store') }}">
                            @csrf
                            <x-ui.form.input name="name" :label="__('vms::vms.requests.installer_name')" required />
                            <x-ui.form.input name="approved_until" type="date" :label="__('vms::vms.requests.approved_until')" />
                            <x-ui.form.actions :cancel="route('vms.requests.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
