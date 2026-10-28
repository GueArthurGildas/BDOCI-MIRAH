@php
    use App\Enums\WitnessReportStatus;
    use App\Support\Authorization\Permission;

    $manage = Gate::allows(Permission::WitnessesManage->value);
    $decided = in_array($report->status, [WitnessReportStatus::Closed, WitnessReportStatus::Forwarded], true);
@endphp

<x-layouts.app :title="__('witnesses::witnesses.detail_title', ['id' => $report->id])">
    <x-ui.page-header :title="__('witnesses::witnesses.detail_title', ['id' => $report->id])" :breadcrumbs="[__('witnesses::witnesses.title') => route('witnesses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="__('witnesses::witnesses.sections.report')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.status')"><x-ui.status-badge :status="$report->status" /></x-ui.detail>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.received_at')">{{ $report->received_at->isoFormat('L LT') }} UTC</x-ui.detail>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.channel')">{{ __($report->channel->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.position')">@if ($report->latitude !== null) {{ $report->latitude }}, {{ $report->longitude }} @endif</x-ui.detail>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.entered_by')">{{ $report->enteredBy?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('witnesses::witnesses.fields.witness')">
                            @if ($report->witness)
                                {{ $report->witness->name }} — {{ __($report->witness->category->label()) }}
                                <div class="text-muted f-12">{{ $report->witness->email }} {{ $report->witness->phone }}</div>
                            @endif
                        </x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                @foreach ($report->reportedVessels as $reported)
                    <x-ui.card :title="__('witnesses::witnesses.sections.vessel', ['rank' => $reported->rank])">
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.known_vessel')">@if ($reported->vessel) <x-ui.vessel-link :vessel="$reported->vessel" /> @endif</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.name')">{{ $reported->name }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.flag')">{{ $reported->flag }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.registration_port')">{{ $reported->registration_port }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.type')">{{ $reported->type }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.ircs')">{{ $reported->ircs }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.registration')">{{ $reported->registration }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.other_marks')">{{ $reported->other_marks }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.gear')">{{ $reported->gear }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.route')">{{ $reported->route }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.activity')">
                                {{ collect([
                                    $reported->is_fishing ? __('witnesses::witnesses.fields.is_fishing') : null,
                                    $reported->is_drifting ? __('witnesses::witnesses.fields.is_drifting') : null,
                                    $reported->is_resupplying ? __('witnesses::witnesses.fields.is_resupplying') : null,
                                    $reported->is_transshipping ? __('witnesses::witnesses.fields.is_transshipping') : null,
                                ])->filter()->join(', ') }}
                            </x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.description')"><span class="text-pre-line">{{ $reported->description }}</span></x-ui.detail>
                        </x-ui.detail-list>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="col-xl-5">
                <x-ui.card :title="__('witnesses::witnesses.sections.usability')">
                    @if ($manage && ! $decided)
                        <form method="POST" action="{{ route('witnesses.usability.save', $report) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.form.select name="is_usable" :label="__('witnesses::witnesses.fields.is_usable')" :options="['1' => __('ui.yes'), '0' => __('ui.no')]" :value="$report->usability ? (int) $report->usability->is_usable : null" required />
                            <x-ui.form.textarea name="interview_report" :label="__('witnesses::witnesses.fields.interview_report')" :value="$report->usability?->interview_report" rows="3" />
                            <x-ui.form.actions :cancel="route('witnesses.show', $report)" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.is_usable')">{{ $report->usability ? ($report->usability->is_usable ? __('ui.yes') : __('ui.no')) : null }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.interview_report')"><span class="text-pre-line">{{ $report->usability?->interview_report }}</span></x-ui.detail>
                        </x-ui.detail-list>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('witnesses::witnesses.sections.operation')">
                    <p class="m-b-10">
                        @if ($report->operation)
                            @can(Permission::OperationsView->value)
                                <a href="{{ route('operations.show', $report->operation) }}">{{ $report->operation->code }}</a>
                            @else
                                {{ $report->operation->code }}
                            @endcan
                        @else
                            <span class="text-muted">{{ __('witnesses::witnesses.no_operation') }}</span>
                        @endif
                    </p>
                    @can(Permission::OperationsCreate->value)
                        @if (! $report->operation)
                            <a href="{{ route('operations.create') }}" class="btn btn-default btn-sm m-b-10"><i class="feather icon-plus"></i> {{ __('witnesses::witnesses.open_operation') }}</a>
                        @endif
                    @endcan
                    @can(Permission::WitnessesCreate->value)
                        <form method="POST" action="{{ route('witnesses.operation.save', $report) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.form.select name="operation_id" :label="__('witnesses::witnesses.fields.operation_id')" :options="$innOperations" :value="$report->operation_id" required />
                            <x-ui.form.actions :cancel="route('witnesses.show', $report)" />
                        </form>
                    @endcan
                </x-ui.card>

                <x-ui.card :title="__('witnesses::witnesses.sections.investigation')">
                    @if ($manage && ! $decided)
                        <form method="POST" action="{{ route('witnesses.investigation.save', $report) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 row">
                                <div class="col-sm-9 offset-sm-3">
                                    @foreach ($elements as $value => $label)
                                        <div class="checkbox-fade fade-in-primary d-block">
                                            <label class="form-label">
                                                <input type="checkbox" name="elements[]" value="{{ $value }}" @checked(in_array($value, $report->investigation?->elements ?? [], true))>
                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span>{{ $label }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <x-ui.form.textarea name="notes" :label="__('witnesses::witnesses.fields.notes')" :value="$report->investigation?->notes" rows="3" />
                            <x-ui.form.actions :cancel="route('witnesses.show', $report)" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.elements')">{{ collect($report->investigation?->elements)->map(fn ($element) => $elements[$element] ?? $element)->join(', ') }}</x-ui.detail>
                            <x-ui.detail :label="__('witnesses::witnesses.fields.notes')"><span class="text-pre-line">{{ $report->investigation?->notes }}</span></x-ui.detail>
                        </x-ui.detail-list>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('witnesses::witnesses.sections.decision')">
                    @foreach ($report->decisions as $decision)
                        <p class="m-b-5">{{ __($decision->decision->label()) }} — {{ $decision->decider->name }} <span class="text-muted f-12">{{ $decision->decided_at->isoFormat('L LT') }}</span></p>
                    @endforeach
                    @if ($manage && ! $decided)
                        <form method="POST" action="{{ route('witnesses.decision.store', $report) }}">
                            @csrf
                            <x-ui.form.select name="decision" :label="__('witnesses::witnesses.fields.decision')" :options="$decisions" required />
                            <x-ui.form.actions :cancel="route('witnesses.show', $report)" />
                        </form>
                    @endif
                    @if ($report->status === WitnessReportStatus::Forwarded)
                        <div class="m-t-10 d-flex gap-2">
                            @can(Permission::InfractionsCreate->value)
                                <a href="{{ route('infractions.create', ['witness_report' => $report->id, 'vessel' => $report->reportedVessels->firstWhere('vessel_id')?->vessel_id, 'operation' => $report->operation_id]) }}" class="btn btn-primary btn-sm"><i class="feather icon-alert-triangle"></i> {{ __('witnesses::witnesses.open_case') }}</a>
                            @endcan
                            @can(Permission::MissionsCreate->value)
                                <a href="{{ route('missions.create', ['witness_report' => $report->id, 'operation' => $report->operation_id]) }}" class="btn btn-default btn-sm"><i class="feather icon-flag"></i> {{ __('witnesses::witnesses.schedule_mission') }}</a>
                            @endcan
                        </div>
                    @endif
                    @if ($report->infractionCases->isNotEmpty())
                        <ul class="list-unstyled m-t-10 m-b-0">
                            @foreach ($report->infractionCases as $case)
                                <li>
                                    @can(Permission::InfractionsView->value)
                                        <a href="{{ route('infractions.show', $case) }}">{{ $case->reference }}</a>
                                    @else
                                        {{ $case->reference }}
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>

                <x-ui.audit-history :subject="$report" />
            </div>
        </div>
    </div>
</x-layouts.app>
