@php($manage = Gate::allows(App\Support\Authorization\Permission::LicensesManage->value))

<x-layouts.app :title="__('licenses::compliance.title', ['vessel' => $vessel->name])">
    <x-ui.page-header :title="__('licenses::compliance.title', ['vessel' => $vessel->name])" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('licenses::compliance.fnp.title')">
                    @if ($vessel->fnpStatus)
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('licenses::compliance.fnp.status')"><x-ui.status-badge :status="$vessel->fnpStatus->status" /></x-ui.detail>
                            <x-ui.detail :label="__('licenses::compliance.fnp.deregistration_reason')">{{ $vessel->fnpStatus->deregistration_reason ? __($vessel->fnpStatus->deregistration_reason->label()) : null }}</x-ui.detail>
                            <x-ui.detail :label="__('licenses::compliance.fnp.status_on')">{{ $vessel->fnpStatus->status_on?->isoFormat('L') }}</x-ui.detail>
                        </x-ui.detail-list>
                    @else
                        <p class="text-muted">{{ __('licenses::compliance.fnp.unknown') }}</p>
                    @endif
                    @if ($manage)
                        <form method="POST" action="{{ route('licenses.vessels.fnp', $vessel) }}" class="m-t-15">
                            @csrf
                            <x-ui.form.select name="status" :label="__('licenses::compliance.fnp.status')" :options="App\Enums\FnpRegistration::options()" :value="$vessel->fnpStatus?->status" required />
                            <x-ui.form.select name="deregistration_reason" :label="__('licenses::compliance.fnp.deregistration_reason')" :options="App\Enums\FnpDeregistrationReason::options()" :value="$vessel->fnpStatus?->deregistration_reason" />
                            <x-ui.form.input name="status_on" type="date" :label="__('licenses::compliance.fnp.status_on')" :value="today()->toDateString()" />
                            <x-ui.form.actions :cancel="route('licenses.vessels.show', $vessel)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('licenses::compliance.vms.title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::compliance.vms.issued_on') }}</th>
                            <th>{{ __('licenses::compliance.vms.expires_on') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($certificates as $certificate)
                            <tr>
                                <td>{{ $certificate->issued_on->isoFormat('L') }}</td>
                                <td>{{ $certificate->expires_on->isoFormat('L') }}</td>
                                <td>@if ($certificate->isExpired())<span class="label label-danger">{{ __('licenses::compliance.expired') }}</span>@endif</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage)
                        <form method="POST" action="{{ route('licenses.vessels.certificates', $vessel) }}" class="p-3">
                            @csrf
                            <x-ui.form.input name="issued_on" type="date" :label="__('licenses::compliance.vms.issued_on')" required />
                            <x-ui.form.input name="expires_on" type="date" :label="__('licenses::compliance.vms.expires_on')" required />
                            <x-ui.form.actions :cancel="route('licenses.vessels.show', $vessel)" />
                        </form>
                    @endif
                </x-ui.card>
            </div>

            <div class="col-xl-6">
                <x-ui.card :title="__('licenses::compliance.external.title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::compliance.external.institution_id') }}</th>
                            <th>{{ __('licenses::compliance.external.number') }}</th>
                            <th>{{ __('licenses::compliance.external.valid_to') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($authorizations as $authorization)
                            <tr>
                                <td>{{ $authorization->institution->name }}</td>
                                <td>{{ $authorization->number }}</td>
                                <td>{{ $authorization->valid_to->isoFormat('L') }}</td>
                                <td>@if ($authorization->isExpired())<span class="label label-danger">{{ __('licenses::compliance.expired') }}</span>@endif</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage)
                        <form method="POST" action="{{ route('licenses.vessels.authorizations', $vessel) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="institution_id" :label="__('licenses::compliance.external.institution_id')" :options="$institutions" required />
                            <x-ui.form.input name="number" :label="__('licenses::compliance.external.number')" maxlength="80" />
                            <x-ui.form.input name="valid_from" type="date" :label="__('licenses::compliance.external.valid_from')" />
                            <x-ui.form.input name="valid_to" type="date" :label="__('licenses::compliance.external.valid_to')" required />
                            <x-ui.form.actions :cancel="route('licenses.vessels.show', $vessel)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('licenses::compliance.licenses')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::licenses.fields.number') }}</th>
                            <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                            <th>{{ __('licenses::licenses.fields.status') }}</th>
                        </x-slot:head>
                        @forelse ($vessel->licenses as $license)
                            <tr>
                                <td><a href="{{ route('licenses.show', $license) }}">{{ $license->number }}</a></td>
                                <td>{{ $license->expires_on->isoFormat('L') }}</td>
                                <td><x-ui.status-badge :status="$license->standing" /></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
