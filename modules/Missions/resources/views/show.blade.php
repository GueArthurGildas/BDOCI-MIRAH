@php
    use App\Enums\MissionStatus;
    use App\Support\Authorization\Permission;

    $manage = Gate::allows(Permission::MissionsManage->value);
    $status = $mission->status;
    $editable = ! in_array($status, [MissionStatus::Closed], true);
    $review = $mission->review;
@endphp

<x-layouts.app :title="$mission->code">
    <x-ui.page-header :title="$mission->code" :subtitle="__($mission->type->label()).' — '.__($mission->nature->label())" :breadcrumbs="[__('missions::missions.title') => route('missions.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('missions::missions.sections.mission')">
                    <x-slot:actions>
                        @if ($status === MissionStatus::Scheduled && $canAuthorize && Gate::allows(Permission::MissionsAuthorize->value))
                            <form data-confirm="{{ __('missions::confirm.authorize') }}" data-confirm-label="{{ __('missions::confirm.authorize_yes') }}" method="POST" action="{{ route('missions.authorize', $mission) }}">@csrf<button class="btn btn-primary btn-sm"><i class="feather icon-check"></i> {{ __('missions::missions.authorize') }}</button></form>
                        @endif
                        @if ($manage)
                            @if ($status === MissionStatus::Authorized)
                                <form data-confirm="{{ __('missions::confirm.start') }}" data-confirm-label="{{ __('missions::confirm.start_yes') }}" method="POST" action="{{ route('missions.start', $mission) }}">@csrf<button class="btn btn-primary btn-sm"><i class="feather icon-play"></i> {{ __('missions::missions.start') }}</button></form>
                            @elseif ($status === MissionStatus::InProgress)
                                <form data-confirm="{{ __('missions::confirm.complete') }}" data-confirm-label="{{ __('missions::confirm.complete_yes') }}" method="POST" action="{{ route('missions.complete', $mission) }}">@csrf<button class="btn btn-success btn-sm"><i class="feather icon-flag"></i> {{ __('missions::missions.complete') }}</button></form>
                            @elseif ($status === MissionStatus::Completed)
                                <form data-confirm="{{ __('missions::confirm.close') }}" data-confirm-label="{{ __('missions::confirm.close_yes') }}" method="POST" action="{{ route('missions.close', $mission) }}">@csrf<button class="btn btn-success btn-sm"><i class="feather icon-lock"></i> {{ __('missions::missions.close') }}</button></form>
                            @endif
                        @endif
                    </x-slot:actions>
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('missions::missions.fields.status')"><x-ui.status-badge :status="$mission->status" /></x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.objectives')"><span class="text-pre-line">{{ $mission->objectives }}</span></x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.origin')">{{ $mission->origin }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.planned_period')">{{ $mission->planned_start_at?->isoFormat('L LT') }} → {{ $mission->planned_end_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.actual_period')">@if ($mission->actual_start_at) {{ $mission->actual_start_at->isoFormat('L LT') }} → {{ $mission->actual_end_at?->isoFormat('L LT') }} @endif</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.zones')">{{ collect($mission->zones)->pluck('name')->filter()->join(', ') }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.points_gps')">{{ collect($mission->points_gps)->map(fn ($point) => $point['latitude'].', '.$point['longitude'])->join(' | ') }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.operation')">
                            @if ($mission->operation)
                                @can(Permission::OperationsView->value)
                                    <a href="{{ route('operations.show', $mission->operation) }}">{{ $mission->operation->code }}</a>
                                @else
                                    {{ $mission->operation->code }}
                                @endcan
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.witness_report')">
                            @if ($mission->witnessReport)
                                @can(Permission::WitnessesView->value)
                                    <a href="{{ route('witnesses.show', $mission->witnessReport) }}">#{{ $mission->witnessReport->id }}</a>
                                @else
                                    #{{ $mission->witnessReport->id }}
                                @endcan
                            @endif
                        </x-ui.detail>
                    </x-ui.detail-list>
                    @if ($mission->authorizations->isNotEmpty())
                        <h6 class="m-t-15">{{ __('missions::missions.sections.authorizations') }}</h6>
                        <ul class="list-unstyled m-0">
                            @foreach ($mission->authorizations as $authorization)
                                <li>{{ __('missions::missions.authorized_by', ['name' => $authorization->authorizer->name, 'role' => __($authorization->authorizing_role->label()), 'date' => $authorization->authorized_at->isoFormat('L LT')]) }}</li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.map')">
                    <x-ui.map height="360px" :markers="$markers" />
                    <p class="text-muted f-12 m-t-10 m-b-0">
                        @forelse ($mission->tracks as $track)
                            {{ __('missions::missions.track_zones', ['zones' => collect($track->zones_crossed)->pluck('name')->join(', ') ?: '—']) }}
                        @empty
                            {{ __('missions::missions.no_track') }}
                        @endforelse
                    </p>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.track.store', $mission) }}" enctype="multipart/form-data" class="m-t-15">
                            @csrf
                            <x-ui.form.input name="file" type="file" :label="__('missions::missions.fields.file')" required />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.units')" :subtitle="__('missions::missions.units_hint')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.observed_at') }}</th>
                            <th>{{ __('missions::missions.fields.level') }}</th>
                            <th>{{ __('missions::missions.fields.unit') }}</th>
                            <th>{{ __('missions::missions.fields.observed_activity') }}</th>
                            <th>{{ __('missions::missions.fields.inspection') }}</th>
                        </x-slot:head>
                        @forelse ($mission->observedUnits as $unit)
                            <tr>
                                <td class="text-nowrap">{{ $unit->observed_at->isoFormat('L LT') }}</td>
                                <td><x-ui.status-badge :status="$unit->level" /> @if ($unit->sighting_source) <span class="text-muted f-12">{{ __($unit->sighting_source->label()) }}</span> @endif</td>
                                <td>
                                    @if ($unit->vessel)
                                        <x-ui.vessel-link :vessel="$unit->vessel" />
                                    @else
                                        {{ $unit->recorded_name }} <span class="text-muted f-12">{{ $unit->recorded_marks }}</span>
                                    @endif
                                </td>
                                <td class="text-wrap">{{ $unit->observed_activity }}</td>
                                <td>{{ $unit->inspection?->reference ?? $unit->inspection?->external_id }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.units.store', $mission) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="level" :label="__('missions::missions.fields.level')" :options="$options['levels']" required />
                            <x-ui.form.select name="sighting_source" :label="__('missions::missions.fields.sighting_source')" :options="$options['sources']" />
                            <x-ui.form.select name="vessel_id" :label="__('missions::missions.fields.vessel_id')" :options="$options['vessels']" />
                            <x-ui.form.input name="recorded_name" :label="__('missions::missions.fields.recorded_name')" />
                            <x-ui.form.input name="recorded_marks" :label="__('missions::missions.fields.recorded_marks')" />
                            <x-ui.form.input name="observed_activity" :label="__('missions::missions.fields.observed_activity')" />
                            <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('missions::missions.fields.latitude')" />
                            <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('missions::missions.fields.longitude')" />
                            <x-ui.form.select name="inspection_id" :label="__('missions::missions.fields.inspection_id')" :options="$options['inspections']" />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.events')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.occurred_at') }}</th>
                            <th>{{ __('missions::missions.fields.body') }}</th>
                        </x-slot:head>
                        @forelse ($mission->events as $event)
                            <tr>
                                <td class="text-nowrap">{{ $event->occurred_at->isoFormat('L LT') }}</td>
                                <td class="text-wrap">{{ $event->body }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.events.store', $mission) }}" class="p-3">
                            @csrf
                            <x-ui.form.textarea name="body" :label="__('missions::missions.fields.body')" rows="2" required />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.briefing')">
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.briefing.save', $mission) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.form.input name="held_at" type="datetime-local" :label="__('missions::missions.fields.held_at')" :value="$mission->briefing?->held_at?->format('Y-m-d\TH:i')" />
                            <x-ui.form.textarea name="notes" :label="__('missions::missions.fields.notes')" :value="$mission->briefing?->notes" rows="2" />
                            <div class="mb-3 row">
                                <div class="col-sm-9 offset-sm-3">
                                    @foreach (App\Enums\MissionChecklistItem::cases() as $item)
                                        <div class="checkbox-fade fade-in-primary d-block">
                                            <label class="form-label">
                                                <input type="checkbox" name="checklist[]" value="{{ $item->value }}" @checked(in_array($item->value, $mission->preparation?->checklist ?? [], true))>
                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                <span>{{ __($item->label()) }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('missions::missions.fields.held_at')">{{ $mission->briefing?->held_at?->isoFormat('L LT') }}</x-ui.detail>
                            <x-ui.detail :label="__('missions::missions.fields.notes')">{{ $mission->briefing?->notes }}</x-ui.detail>
                        </x-ui.detail-list>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.debriefing')">
                    @if ($manage && $editable && in_array($status, [MissionStatus::InProgress, MissionStatus::Completed], true))
                        <form method="POST" action="{{ route('missions.debriefing.save', $mission) }}">
                            @csrf
                            @method('PUT')
                            <x-ui.form.input name="held_at" type="datetime-local" :label="__('missions::missions.fields.held_at')" :value="$mission->debriefing?->held_at?->format('Y-m-d\TH:i')" />
                            <x-ui.form.textarea name="notes" :label="__('missions::missions.fields.notes')" :value="$mission->debriefing?->notes" rows="3" />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('missions::missions.fields.held_at')">{{ $mission->debriefing?->held_at?->isoFormat('L LT') }}</x-ui.detail>
                            <x-ui.detail :label="__('missions::missions.fields.notes')">{{ $mission->debriefing?->notes }}</x-ui.detail>
                        </x-ui.detail-list>
                    @endif
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                <x-ui.card :title="__('missions::missions.sections.review')" :subtitle="__('missions::missions.review_hint')">
                    <x-slot:actions>
                        @if ($manage && $editable)
                            <form method="POST" action="{{ route('missions.review', $mission) }}">@csrf<button class="btn btn-default btn-sm"><i class="feather icon-refresh-cw"></i> {{ __('missions::missions.recompute') }}</button></form>
                        @endif
                    </x-slot:actions>
                    <x-ui.detail-list>
                        @foreach ($computed as $field => $value)
                            <x-ui.detail :label="__('missions::missions.review.'.$field)">{{ $value ?? '—' }}</x-ui.detail>
                        @endforeach
                    </x-ui.detail-list>
                    <p class="text-muted f-12 m-t-10 m-b-0">{{ $review ? __('missions::missions.calculated_at', ['date' => $review->calculated_at->isoFormat('L LT')]) : __('missions::missions.not_calculated') }}</p>
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.participants')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.agent') }}</th>
                            <th>{{ __('missions::missions.fields.role') }}</th>
                            <th>{{ __('missions::missions.fields.agency') }}</th>
                        </x-slot:head>
                        @forelse ($mission->participants as $participant)
                            <tr>
                                <td>{{ $participant->agent?->name ?? '—' }} @if ($participant->is_mission_lead) <span class="label label-primary">{{ __('missions::missions.lead') }}</span> @endif</td>
                                <td>{{ $participant->role }}</td>
                                <td>{{ $participant->agency }} <span class="text-muted f-12">{{ $participant->grade }}</span></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.participants.store', $mission) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="agent_id" :label="__('missions::missions.fields.agent')" :options="$options['agents']" />
                            <x-ui.form.input name="role" :label="__('missions::missions.fields.role')" />
                            <x-ui.form.input name="agency" :label="__('missions::missions.fields.agency')" />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.assets')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.asset') }}</th>
                            <th>{{ __('missions::missions.fields.usage') }}</th>
                        </x-slot:head>
                        @forelse ($mission->missionAssets as $missionAsset)
                            <tr>
                                <td>{{ $missionAsset->asset->label }} <div class="text-muted f-12">{{ __($missionAsset->asset->type->label()) }}</div></td>
                                <td class="f-12">
                                    @if ($manage && $editable)
                                        <form method="POST" action="{{ route('missions.assets.usage', $missionAsset) }}" class="d-flex flex-wrap gap-1">
                                            @csrf
                                            @method('PUT')
                                            <input name="engine_hours" type="number" step="0.1" min="0" value="{{ $missionAsset->engine_hours }}" class="form-control form-control-sm" style="width: 5rem" placeholder="{{ __('missions::missions.fields.engine_hours') }}" title="{{ __('missions::missions.fields.engine_hours') }}">
                                            <input name="nautical_miles" type="number" step="0.1" min="0" value="{{ $missionAsset->nautical_miles }}" class="form-control form-control-sm" style="width: 5rem" placeholder="{{ __('missions::missions.fields.nautical_miles') }}" title="{{ __('missions::missions.fields.nautical_miles') }}">
                                            <input name="consumed_fuel_l" type="number" step="0.1" min="0" value="{{ $missionAsset->consumed_fuel_l }}" class="form-control form-control-sm" style="width: 5rem" placeholder="{{ __('missions::missions.fields.consumed_fuel_l') }}" title="{{ __('missions::missions.fields.consumed_fuel_l') }}">
                                            <button class="btn btn-default btn-sm"><i class="feather icon-save"></i></button>
                                        </form>
                                    @else
                                        {{ $missionAsset->engine_hours }} h — {{ $missionAsset->nautical_miles }} NM — {{ $missionAsset->consumed_fuel_l }} L
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.assets.store', $mission) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="asset_id" :label="__('missions::missions.fields.asset')" :options="$options['assets']" required />
                            <x-ui.form.input name="planned_fuel_l" type="number" step="0.1" :label="__('missions::missions.fields.planned_fuel_l')" />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.observers')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.observer') }}</th>
                            <th>{{ __('missions::missions.fields.vessel_id') }}</th>
                        </x-slot:head>
                        @forelse ($mission->observerBoardings as $boarding)
                            <tr>
                                <td>{{ $boarding->observer->person->full_name }}</td>
                                <td>{{ $boarding->vessel?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                    @if ($manage && $editable)
                        <form method="POST" action="{{ route('missions.observers.store', $mission) }}" class="p-3">
                            @csrf
                            <x-ui.form.select name="observer_id" :label="__('missions::missions.fields.observer')" :options="$options['observers']" />
                            <x-ui.form.input name="name" :label="__('missions::missions.fields.observer_name')" />
                            <x-ui.form.select name="vessel_id" :label="__('missions::missions.fields.vessel_id')" :options="$options['vessels']" />
                            <x-ui.form.actions :cancel="route('missions.show', $mission)" />
                        </form>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.sections.cases')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.fields.reference') }}</th>
                            <th>{{ __('missions::missions.fields.vessel_id') }}</th>
                            <th>{{ __('missions::missions.fields.status') }}</th>
                        </x-slot:head>
                        @forelse ($mission->infractionCases as $case)
                            <tr>
                                <td>
                                    @can(Permission::InfractionsView->value)
                                        <a href="{{ route('infractions.show', $case) }}">{{ $case->reference }}</a>
                                    @else
                                        {{ $case->reference }}
                                    @endcan
                                </td>
                                <td>{{ $case->vessel->name }}</td>
                                <td><x-ui.status-badge :status="$case->status" /></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                    @can(Permission::InfractionsCreate->value)
                        <div class="p-3"><a href="{{ route('infractions.create', ['mission' => $mission->id]) }}" class="btn btn-default btn-sm"><i class="feather icon-alert-triangle"></i> {{ __('missions::missions.open_case') }}</a></div>
                    @endcan
                </x-ui.card>

                <x-ui.audit-history :subject="$mission" />
            </div>
        </div>
    </div>
</x-layouts.app>
