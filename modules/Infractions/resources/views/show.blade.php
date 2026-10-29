@php
    use App\Enums\FollowUpType;
    use App\Enums\InfractionQualification;
    use App\Enums\PrecautionaryMeasureType;
    use App\Enums\SeizureDecision;
    use App\Support\Authorization\Permission;

    $manage = Gate::allows(Permission::InfractionsManage->value);
    $decide = Gate::allows(Permission::InfractionsDecide->value);
    $decideMeasures = Gate::allows(Permission::InfractionsDecideMeasures->value);
    $open = ! $case->isClosed();
@endphp

<x-layouts.app :title="$case->reference">
    <x-ui.page-header :title="$case->reference" :subtitle="$case->upheld->map(fn ($upheld) => $upheld->type->label)->implode(', ') ?: __('infractions::infractions.no_upheld')" :breadcrumbs="[__('infractions::infractions.title') => route('infractions.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card data-guide="case" :title="__('infractions::infractions.sections.case')">
                    @if ($manage && $open)
                        <form method="POST" action="{{ route('infractions.update', $case) }}">
                            @csrf
                            @method('PATCH')
                            <x-ui.form.textarea name="facts" :label="__('infractions::infractions.fields.facts')" :value="$case->facts" rows="3" required />
                            <x-ui.form.select name="qualification" :label="__('infractions::infractions.fields.qualification')" :options="InfractionQualification::options()" :value="$case->qualification" required />
                            <x-ui.form.textarea name="captain_observations" :label="__('infractions::infractions.fields.captain_observations')" :value="$case->captain_observations" rows="3" />
                            <x-ui.form.actions :cancel="route('infractions.index')" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('infractions::infractions.fields.facts')"><span class="text-pre-line">{{ $case->facts }}</span></x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.qualification')"><x-ui.status-badge :status="$case->qualification" /></x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.captain_observations')"><span class="text-pre-line">{{ $case->captain_observations }}</span></x-ui.detail>
                        </x-ui.detail-list>
                    @endif
                </x-ui.card>

                <x-ui.card data-guide="upheld" :title="__('infractions::infractions.sections.upheld')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('infractions::infractions.fields.code') }}</th>
                            <th>{{ __('infractions::infractions.fields.label') }}</th>
                            <th>{{ __('infractions::infractions.fields.legal_basis') }}</th>
                            <th>{{ __('infractions::infractions.fields.severity') }}</th>
                            <th>{{ __('infractions::infractions.fields.potential_penalty') }}</th>
                        </x-slot:head>
                        @forelse ($case->upheld as $upheld)
                            <tr>
                                <td>{{ $upheld->type->code }}</td>
                                <td class="text-wrap">{{ $upheld->type->label }}</td>
                                <td class="text-wrap">{{ $upheld->type->legal_basis }}</td>
                                <td>{{ $upheld->type->severityLabel() }}</td>
                                <td class="text-wrap">{{ $upheld->type->potential_penalty }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $open)
                        <form method="POST" action="{{ route('infractions.upheld.store', $case) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="infraction_type_id" :label="__('infractions::infractions.fields.type')" :options="$types" required />
                            <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card data-guide="evidence" :title="__('infractions::infractions.sections.evidence')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('infractions::infractions.fields.description') }}</th>
                            <th>{{ __('infractions::infractions.fields.document') }}</th>
                        </x-slot:head>
                        @forelse ($case->evidence as $item)
                            <tr>
                                <td class="text-wrap">{{ $item->description }}</td>
                                <td>
                                    @if ($item->document)
                                        <a href="{{ route('infractions.documents.download', [$case, $item->document]) }}">{{ $item->document->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $open)
                        <form method="POST" action="{{ route('infractions.evidence.store', $case) }}" enctype="multipart/form-data" class="p-3">
                            @csrf
                            <x-ui.form.input name="description" :label="__('infractions::infractions.fields.description')" required />
                            <x-ui.form.input name="file" type="file" :label="__('infractions::infractions.fields.file')" />
                            <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                        </form>
                    @endif
                </x-ui.card>

                <div class="row">
                    <div class="col-lg-6">
                        <x-ui.card :title="__('infractions::infractions.sections.seals')" flush>
                            <x-ui.table>
                                <x-slot:head>
                                    <th>{{ __('infractions::infractions.fields.number') }}</th>
                                    <th>{{ __('infractions::infractions.fields.placed_at') }}</th>
                                </x-slot:head>
                                @forelse ($case->seals as $seal)
                                    <tr>
                                        <td>{{ $seal->number }}</td>
                                        <td>{{ $seal->placed_at?->isoFormat('L LT') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <x-ui.empty-row colspan="2" />
                                @endforelse
                            </x-ui.table>
                            @if ($manage && $open)
                                <form method="POST" action="{{ route('infractions.seals.store', $case) }}" class="p-3">
                                    @csrf
                                    <x-ui.form.input name="number" :label="__('infractions::infractions.fields.number')" required />
                                    <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                                </form>
                            @endif
                        </x-ui.card>
                    </div>
                    <div class="col-lg-6">
                        <x-ui.card :title="__('infractions::infractions.sections.seizures')" flush>
                            <x-ui.table>
                                <x-slot:head>
                                    <th>{{ __('infractions::infractions.fields.item') }}</th>
                                    <th>{{ __('infractions::infractions.fields.seizure_decision') }}</th>
                                </x-slot:head>
                                @forelse ($case->seizures as $seizure)
                                    <tr>
                                        <td class="text-wrap">{{ $seizure->item }}</td>
                                        <td>{{ $seizure->decision ? __($seizure->decision->label()) : '—' }}</td>
                                    </tr>
                                @empty
                                    <x-ui.empty-row colspan="2" />
                                @endforelse
                            </x-ui.table>
                            @if ($manage && $open)
                                <form method="POST" action="{{ route('infractions.seizures.store', $case) }}" class="p-3">
                                    @csrf
                                    <x-ui.form.input name="item" :label="__('infractions::infractions.fields.item')" required />
                                    <x-ui.form.select name="decision" :label="__('infractions::infractions.fields.seizure_decision')" :options="SeizureDecision::options()" />
                                    <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                                </form>
                            @endif
                        </x-ui.card>
                    </div>
                </div>

                <x-ui.card data-guide="measures" :title="__('infractions::infractions.sections.measures')" :subtitle="__('infractions::infractions.measures_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('infractions::infractions.fields.measure_type') }}</th>
                            <th>{{ __('infractions::infractions.fields.proposed_by') }}</th>
                            <th>{{ __('infractions::infractions.fields.decided_by') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($case->measures as $measure)
                            <tr>
                                <td>{{ __($measure->type->label()) }}</td>
                                <td>{{ $measure->proposer?->name ?? '—' }}</td>
                                <td>
                                    @if ($measure->isDecided())
                                        {{ $measure->decider?->name }} <span class="text-muted f-12">{{ $measure->decided_at->isoFormat('L LT') }}</span>
                                    @else
                                        <span class="label label-warning">{{ __('infractions::infractions.pending') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if (! $measure->isDecided() && $decideMeasures)
                                        <form data-confirm="{{ __('infractions::confirm.measure') }}" data-confirm-label="{{ __('infractions::confirm.measure_yes') }}" method="POST" action="{{ route('infractions.measures.decide', $measure) }}">
                                            @csrf
                                            <button class="btn btn-primary btn-sm"><i class="feather icon-check"></i> {{ __('infractions::infractions.decide_measure') }}</button>
                                        </form>
                                    @elseif ($measure->isDecided() && in_array($measure->type, [PrecautionaryMeasureType::LicenseSuspension, PrecautionaryMeasureType::LicenseWithdrawal], true) && $case->vessel->currentLicense && Gate::allows(Permission::LicensesSuspend->value))
                                        <a href="{{ route('licenses.show', [$case->vessel->currentLicense, 'infraction_case' => $case->id]) }}" class="btn btn-default btn-sm">{{ __('infractions::infractions.license_effect') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $open)
                        <form method="POST" action="{{ route('infractions.measures.store', $case) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="type" :label="__('infractions::infractions.fields.measure_type')" :options="PrecautionaryMeasureType::options()" required />
                            <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card data-guide="violation_report" :title="__('infractions::infractions.sections.violation_report')">
                    @if ($case->violationReport)
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('infractions::infractions.fields.number')">{{ $case->violationReport->number }}</x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.notified_at')">{{ $case->violationReport->notified_at?->isoFormat('L LT') }}</x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.is_signature_refused')">{{ $case->violationReport->is_signature_refused ? __('ui.yes') : __('ui.no') }}</x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.document')">
                                @if ($case->violationReport->document)
                                    <a href="{{ route('infractions.documents.download', [$case, $case->violationReport->document]) }}"><i class="feather icon-file-text"></i> {{ $case->violationReport->document->name }}</a>
                                @endif
                            </x-ui.detail>
                        </x-ui.detail-list>
                    @elseif ($manage && $open)
                        <form data-confirm="{{ __('infractions::confirm.violation_report') }}" data-confirm-label="{{ __('infractions::confirm.violation_report_yes') }}" method="POST" action="{{ route('infractions.violation-report.store', $case) }}">
                            @csrf
                            <x-ui.form.input name="number" :label="__('infractions::infractions.fields.number')" :placeholder="__('infractions::infractions.automatic_number')" />
                            <x-ui.form.input name="notified_at" type="datetime-local" :label="__('infractions::infractions.fields.notified_at')" />
                            <x-ui.form.checkbox name="is_signature_refused" :label="__('infractions::infractions.fields.is_signature_refused')" />
                            <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                        </form>
                    @else
                        <p class="text-muted m-0">{{ __('infractions::infractions.no_violation_report') }}</p>
                    @endif
                </x-ui.card>

                <x-ui.card data-guide="follow_ups" :title="__('infractions::infractions.sections.follow_ups')">
                    @forelse ($case->followUps as $followUp)
                        <div class="m-b-20">
                            <h6>{{ __($followUp->type->label()) }}
                                <span class="text-muted f-12">{{ $followUp->opened_at->isoFormat('L') }}@if ($followUp->closed_at) → {{ $followUp->closed_at->isoFormat('L') }}@endif</span>
                            </h6>
                            @foreach ($followUp->decisions as $decisionRecord)
                                <div class="m-l-20 m-b-15">
                                    <div>
                                        <i class="feather icon-check-square text-muted"></i> {{ $decisionRecord->label }}
                                        @if ($decisionRecord->is_penalty)
                                            <span class="label label-danger">{{ __('infractions::infractions.penalty') }}</span>
                                        @endif
                                        <span class="text-muted f-12">{{ $decisionRecord->decided_at->isoFormat('L LT') }}</span>
                                        @if ($decisionDocuments[$decisionRecord->id] ?? null)
                                            <a href="{{ route('infractions.documents.download', [$case, $decisionDocuments[$decisionRecord->id]]) }}" class="m-l-10"><i class="feather icon-file-text"></i></a>
                                        @endif
                                    </div>
                                    @foreach ($decisionRecord->payments as $payment)
                                        <div class="m-l-20 f-12"><i class="feather icon-credit-card text-muted"></i> {{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }} @if ($payment->paid_on) — {{ $payment->paid_on->isoFormat('L') }} @endif</div>
                                    @endforeach
                                    @foreach ($decisionRecord->appeals as $appeal)
                                        <div class="m-l-20 f-12">
                                            <i class="feather icon-corner-up-left text-muted"></i> {{ __('infractions::infractions.appeal_filed_on', ['date' => $appeal->filed_on->isoFormat('L')]) }}
                                            @if ($appeal->outcome) — {{ $appeal->outcome }} @elseif ($decide)
                                                <form data-confirm="{{ __('infractions::confirm.appeal_outcome') }}" data-confirm-label="{{ __('infractions::confirm.appeal_outcome_yes') }}" method="POST" action="{{ route('infractions.appeals.outcome', $appeal) }}" class="d-inline-flex gap-1 m-l-10">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input name="outcome" class="form-control form-control-sm" placeholder="{{ __('infractions::infractions.fields.outcome') }}" required>
                                                    <button class="btn btn-default btn-sm">{{ __('ui.save') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if ($decide)
                                        <div class="m-l-20 m-t-10">
                                            <form method="POST" action="{{ route('infractions.payments.store', $decisionRecord) }}" class="d-flex flex-wrap gap-1 m-b-5">
                                                @csrf
                                                <input name="amount" type="number" step="0.01" min="0.01" class="form-control form-control-sm w-auto" placeholder="{{ __('infractions::infractions.fields.amount') }}" required>
                                                <input name="currency" value="XOF" maxlength="3" class="form-control form-control-sm w-auto" style="max-width: 5rem">
                                                <input name="paid_on" type="date" class="form-control form-control-sm w-auto">
                                                <button class="btn btn-default btn-sm"><i class="feather icon-credit-card"></i> {{ __('infractions::infractions.record_payment') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('infractions.appeals.store', $decisionRecord) }}" class="d-flex flex-wrap gap-1">
                                                @csrf
                                                <input name="filed_on" type="date" class="form-control form-control-sm w-auto" required>
                                                <button class="btn btn-default btn-sm"><i class="feather icon-corner-up-left"></i> {{ __('infractions::infractions.file_appeal') }}</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            @if ($decide && $open && $followUp->closed_at === null)
                                <form data-confirm="{{ __('infractions::confirm.decision') }}" data-confirm-label="{{ __('infractions::confirm.decision_yes') }}" method="POST" action="{{ route('infractions.decisions.store', $followUp) }}" class="m-l-20">
                                    @csrf
                                    <x-ui.form.input name="label" :label="__('infractions::infractions.fields.decision')" required />
                                    <x-ui.form.checkbox name="is_penalty" :label="__('infractions::infractions.fields.is_penalty')" />
                                    <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">{{ __('infractions::infractions.no_follow_up') }}</p>
                    @endforelse

                    @if ($decide && $open)
                        <form method="POST" action="{{ route('infractions.follow-ups.store', $case) }}" class="border-top p-t-15">
                            @csrf
                            <x-ui.form.select name="type" :label="__('infractions::infractions.fields.follow_up_type')" :options="FollowUpType::options()" required />
                            <x-ui.form.actions :cancel="route('infractions.show', $case)" />
                        </form>
                    @endif
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                <x-ui.card data-guide="status" :title="__('infractions::infractions.sections.status')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('infractions::infractions.fields.status')"><x-ui.status-badge :status="$case->status" /></x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.qualification')"><x-ui.status-badge :status="$case->qualification" /></x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.opened_at')">{{ $case->opened_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.opened_by')">{{ $case->opener?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.vessel')"><x-ui.vessel-link :vessel="$case->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.captain')">{{ $case->captain?->full_name }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.shipowner')">{{ $case->shipowner?->name }}</x-ui.detail>
                    </x-ui.detail-list>
                    @if ($decide && $open)
                        <form data-confirm="{{ __('infractions::confirm.close') }}" data-confirm-label="{{ __('infractions::confirm.close_yes') }}" method="POST" action="{{ route('infractions.close', $case) }}" class="p-t-15">
                            @csrf
                            <button class="btn btn-success btn-sm"><i class="feather icon-check-circle"></i> {{ __('infractions::infractions.close_case') }}</button>
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('infractions::infractions.sections.repeat')" :subtitle="__('infractions::infractions.repeat_hint', ['threshold' => $repeat->threshold])">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('infractions::infractions.fields.vessel')">
                            {{ $repeat->vesselCases }} @if ($repeat->isVesselRepeat()) <span class="label label-danger">{{ __('infractions::infractions.repeat') }}</span> @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.captain')">
                            @if ($repeat->captainCases !== null) {{ $repeat->captainCases }} @if ($repeat->isCaptainRepeat()) <span class="label label-danger">{{ __('infractions::infractions.repeat') }}</span> @endif @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.shipowner')">
                            @if ($repeat->shipownerCases !== null) {{ $repeat->shipownerCases }} @if ($repeat->isShipownerRepeat()) <span class="label label-danger">{{ __('infractions::infractions.repeat') }}</span> @endif @endif
                        </x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                <x-ui.card :title="__('infractions::infractions.sections.origins')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('infractions::infractions.fields.inspection')">
                            @if ($case->inspection)
                                @can(App\Support\Authorization\Permission::InspectionsView->value)
                                    <a href="{{ route('inspections.show', $case->inspection) }}">{{ $case->inspection->reference ?? $case->inspection->external_id }}</a>
                                @else
                                    {{ $case->inspection->reference ?? $case->inspection->external_id }}
                                @endcan
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.mission')">
                            @if ($case->mission)
                                @can(App\Support\Authorization\Permission::MissionsView->value)
                                    <a href="{{ route('missions.show', $case->mission) }}">{{ $case->mission->code }}</a>
                                @else
                                    {{ $case->mission->code }}
                                @endcan
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.witness_report')">
                            @if ($case->witnessReport)
                                @can(App\Support\Authorization\Permission::WitnessesView->value)
                                    <a href="{{ route('witnesses.show', $case->witnessReport) }}">#{{ $case->witnessReport->id }}</a>
                                @else
                                    #{{ $case->witnessReport->id }}
                                @endcan
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.operation')">
                            @if ($case->operation)
                                @can(App\Support\Authorization\Permission::OperationsView->value)
                                    <a href="{{ route('operations.show', $case->operation) }}">{{ $case->operation->code }}</a>
                                @else
                                    {{ $case->operation->code }}
                                @endcan
                            @endif
                        </x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                @if ($case->suspensions->isNotEmpty() || $case->revocations->isNotEmpty())
                    <x-ui.card :title="__('infractions::infractions.sections.license_effects')">
                        <ul class="list-unstyled m-0">
                            @foreach ($case->suspensions as $suspension)
                                <li>{{ __('infractions::infractions.license_suspended', ['number' => $suspension->license->number, 'date' => $suspension->started_on->isoFormat('L')]) }}</li>
                            @endforeach
                            @foreach ($case->revocations as $revocation)
                                <li>{{ __('infractions::infractions.license_revoked', ['number' => $revocation->license->number, 'date' => $revocation->revoked_on->isoFormat('L')]) }}</li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-ui.audit-history :subject="$case" />
            </div>
        </div>
    </div>
</x-layouts.app>
