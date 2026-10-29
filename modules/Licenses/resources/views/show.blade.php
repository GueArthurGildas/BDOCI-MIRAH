@php($manage = Gate::allows(App\Support\Authorization\Permission::LicensesManage->value))

<x-layouts.app :title="__('licenses::licenses.detail_title', ['number' => $license->number])">
    <x-ui.page-header :title="__('licenses::licenses.detail_title', ['number' => $license->number])" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('licenses::licenses.identity')">
                    <x-slot:actions>
                        @if ($manage)
                            <a href="{{ route('licenses.edit', $license) }}" class="btn btn-default btn-sm"><i class="feather icon-edit"></i> {{ __('ui.edit') }}</a>
                        @endif
                    </x-slot:actions>
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('licenses::licenses.fields.number')">{{ $license->number }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.vessel')"><x-ui.vessel-link :vessel="$license->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.segment')">{{ $license->segment ? __($license->segment->label()) : null }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.issued_on')">{{ $license->issued_on?->isoFormat('L') }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.expires_on')">{{ $license->expires_on->isoFormat('L') }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.days_left')">{{ $license->daysUntilExpiry() >= 0 ? $license->daysUntilExpiry() : null }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.status')"><x-ui.status-badge :status="$license->standing" /></x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.origin')">{{ __($license->origin->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.entered_by')">{{ $license->enteredBy?->name }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                <x-ui.card :title="__('licenses::licenses.scope')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('licenses::licenses.fields.zones')">{{ $license->zones->pluck('name')->join(', ') }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.gears')">{{ $license->gears->pluck('label')->join(', ') }}</x-ui.detail>
                        <x-ui.detail :label="__('licenses::licenses.fields.species')">{{ $license->species->pluck('name')->join(', ') }}</x-ui.detail>
                    </x-ui.detail-list>
                    @if ($license->conditions->isNotEmpty())
                        <h6 class="m-t-15">{{ __('licenses::licenses.fields.conditions') }}</h6>
                        <ul>
                            @foreach ($license->conditions as $condition)
                                <li>{{ $condition->body }}</li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('licenses::licenses.documents.title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::licenses.documents.name') }}</th>
                            <th>{{ __('licenses::licenses.documents.added_on') }}</th>
                        </x-slot:head>
                        @forelse ($license->documents as $link)
                            <tr>
                                <td><a href="{{ route('licenses.documents.download', $link) }}">{{ $link->document->name }}</a></td>
                                <td>{{ $link->created_at->isoFormat('L') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage)
                        <form method="POST" action="{{ route('licenses.documents.store', $license) }}" enctype="multipart/form-data" class="p-3">
                            @csrf
                            <x-ui.form.input name="file" type="file" :label="__('licenses::licenses.documents.file')" accept="application/pdf" required />
                            <div class="mb-3 row">
                                <label for="vessel_ids" class="form-label col-sm-3 col-form-label">{{ __('licenses::licenses.documents.also_vessels') }}</label>
                                <div class="col-sm-9">
                                    <select id="vessel_ids" name="vessel_ids[]" multiple size="4" class="form-select">
                                        @foreach ($vessels as $id => $label)
                                            @continue($id === $license->vessel_id)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <x-ui.form.actions :cancel="route('licenses.show', $license)" />
                        </form>
                    @endif
                </x-ui.card>
            </div>

            <div class="col-xl-6">
                <x-ui.card :title="__('licenses::licenses.suspension.title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::licenses.suspension.reason') }}</th>
                            <th>{{ __('licenses::licenses.suspension.started_on') }}</th>
                            <th>{{ __('licenses::licenses.suspension.planned_end_on') }}</th>
                            <th>{{ __('licenses::licenses.suspension.lifted_on') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($license->suspensions as $suspension)
                            <tr>
                                <td>{{ __($suspension->reason->label()) }}</td>
                                <td>{{ $suspension->started_on->isoFormat('L') }}</td>
                                <td>
                                    {{ $suspension->planned_end_on?->isoFormat('L') ?? '—' }}
                                    @if ($suspension->isOverdue() && $license->standing !== App\Enums\LicenseStatus::Revoked)
                                        <span class="label label-danger">{{ __('licenses::licenses.suspension.overdue') }}</span>
                                    @endif
                                </td>
                                <td>{{ $suspension->lifted_on?->isoFormat('L') ?? '—' }}</td>
                                <td class="text-end">
                                    @can(App\Support\Authorization\Permission::LicensesSuspend->value)
                                        @if ($suspension->isInForce())
                                            <form data-confirm="{{ __('licenses::confirm.lift') }}" data-confirm-label="{{ __('licenses::confirm.lift_yes') }}" method="POST" action="{{ route('licenses.suspensions.lift', $suspension) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-mini">{{ __('licenses::licenses.suspension.lift') }}</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                    @if ($license->status !== App\Enums\LicenseRecordStatus::Revoked)
                        @can(App\Support\Authorization\Permission::LicensesSuspend->value)
                            <form data-confirm="{{ __('licenses::confirm.suspend') }}" data-confirm-label="{{ __('licenses::confirm.suspend_yes') }}" method="POST" action="{{ route('licenses.suspensions.store', $license) }}" class="p-3">
                                @csrf
                                <input type="hidden" name="infraction_case_id" value="{{ request('infraction_case') }}">
                                <p class="text-muted f-12">{{ __('licenses::licenses.suspension.rule', ['months' => config('surveillance.license_suspension_max_months')]) }}</p>
                                <x-ui.form.select name="reason" :label="__('licenses::licenses.suspension.reason')" :options="App\Enums\SuspensionReason::options()" required />
                                <x-ui.form.input name="started_on" type="date" :label="__('licenses::licenses.suspension.started_on')" :value="today()->toDateString()" required />
                                <x-ui.form.input name="planned_end_on" type="date" :label="__('licenses::licenses.suspension.planned_end_on')" />
                                <x-ui.form.actions :cancel="route('licenses.show', $license)" />
                            </form>
                        @endcan
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('licenses::licenses.revocation.title')">
                    @if ($license->revocation)
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('licenses::licenses.revocation.reason')">{{ $license->revocation->reason }}</x-ui.detail>
                            <x-ui.detail :label="__('licenses::licenses.revocation.revoked_on')">{{ $license->revocation->revoked_on->isoFormat('L') }}</x-ui.detail>
                            <x-ui.detail :label="__('licenses::licenses.revocation.is_repeat_offense')">{{ $license->revocation->is_repeat_offense ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                            <x-ui.detail :label="__('licenses::licenses.revocation.decided_by')">{{ $license->revocation->decider?->name }}</x-ui.detail>
                        </x-ui.detail-list>
                    @else
                        @can(App\Support\Authorization\Permission::LicensesRevoke->value)
                            <form data-confirm="{{ __('licenses::confirm.revoke') }}" data-confirm-label="{{ __('licenses::confirm.revoke_yes') }}" method="POST" action="{{ route('licenses.revocation.store', $license) }}">
                                @csrf
                                <input type="hidden" name="infraction_case_id" value="{{ request('infraction_case') }}">
                                <x-ui.form.input name="reason" :label="__('licenses::licenses.revocation.reason')" maxlength="255" required />
                                <x-ui.form.input name="revoked_on" type="date" :label="__('licenses::licenses.revocation.revoked_on')" :value="today()->toDateString()" required />
                                <x-ui.form.checkbox name="is_repeat_offense" :label="__('licenses::licenses.revocation.is_repeat_offense')" />
                                <x-ui.form.actions :cancel="route('licenses.show', $license)" />
                            </form>
                        @else
                            <p class="text-muted m-0">{{ __('licenses::licenses.revocation.none') }}</p>
                        @endcan
                    @endif
                </x-ui.card>

                <a href="{{ route('licenses.vessels.show', $license->vessel) }}" class="btn btn-default">{{ __('licenses::licenses.compliance_link') }}</a>
            </div>
        </div>
    </div>
</x-layouts.app>
