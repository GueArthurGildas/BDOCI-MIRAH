@php
    $scales = app(App\Support\Operations\WeatherScales::class);
    $scaleLabel = fn (string $scale, ?string $value) => $value === null ? null : ($scales->options($scale)[$value] ?? $value);
    $context = $operation->context;
    $actorFor = fn (App\Models\Vessel $vessel, array $roles) => $vessel->actors->first(fn ($actor) => $actor->ended_on === null && in_array($actor->role, $roles, true));
    $holder = fn ($actor) => $actor?->legalEntity ?? $actor?->person;
    $manager = $operation->personnel->first(fn ($user) => $user->pivot->role === Modules\Operations\Actions\OpenOperation::MANAGER_ROLE);
    $operator = $operation->personnel->first(fn ($user) => $user->pivot->role === Modules\Operations\Actions\OpenOperation::LEAD_ROLE) ?? $operation->creator;
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
        <tbody>
            <tr>
                <th scope="row" class="w-25">{{ __('operations::operations.fields.type') }}</th>
                <td colspan="3">
                    @foreach (App\Enums\OperationCategoryCode::cases() as $code)
                        <span @class(['label', 'label-primary' => $operation->category->code === $code, 'text-muted' => $operation->category->code !== $code])>{{ strtoupper($code->value) }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.number') }}</th>
                <td colspan="3">{{ $operation->code }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.first_event_date') }}</th>
                <td colspan="3">{{ $operation->started_at->format('d/m/Y H:i') }}</td>
            </tr>
            @if ($context?->first_event)
                <tr><td colspan="4" class="text-pre-line">{{ $context->first_event }}</td></tr>
            @endif

            <tr><th colspan="4">{{ __('operations::operations.weather_title') }}</th></tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.temperature') }}</th>
                <td>{{ $context?->temperature }}</td>
                <th scope="row">{{ __('operations::operations.fields.pressure') }}</th>
                <td>{{ $context?->pressure }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.visibility') }}</th>
                <td>{{ $scaleLabel(App\Support\Operations\WeatherScales::VISIBILITY, $context?->visibility) }}</td>
                <th scope="row">{{ __('operations::operations.fields.cloud_cover') }}</th>
                <td>{{ $scaleLabel(App\Support\Operations\WeatherScales::CLOUD_COVER, $context?->cloud_cover) }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.wind_beaufort') }}</th>
                <td>{{ $context?->wind_beaufort }}</td>
                <th scope="row">{{ __('operations::operations.fields.sea_state') }}</th>
                <td>{{ $scaleLabel(App\Support\Operations\WeatherScales::SEA_STATE, $context?->sea_state) }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.weather_notes') }}</th>
                <td colspan="3" class="text-pre-line">{{ $context?->weather_notes }}</td>
            </tr>

            @foreach ($operation->vessels as $vessel)
                @php
                    $owner = $holder($actorFor($vessel, [App\Enums\ActorRole::Shipowner, App\Enums\ActorRole::Owner]));
                    $agent = $holder($actorFor($vessel, [App\Enums\ActorRole::LocalAgent]));
                    $captain = $holder($actorFor($vessel, [App\Enums\ActorRole::Captain, App\Enums\ActorRole::FishingCaptain]));
                @endphp
                <tr><th colspan="4">{{ __('operations::operations.vessel_title') }}</th></tr>
                <tr>
                    <th scope="row">{{ __('operations::operations.fields.vessel_name') }}</th>
                    <td><x-ui.vessel-link :vessel="$vessel" /></td>
                    <th scope="row">{{ __('operations::operations.fields.flag') }}</th>
                    <td>{{ $vessel->flagName() }}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('operations::operations.fields.imo') }}</th>
                    <td>{{ $vessel->imo_number ?? $vessel->identifierValue(App\Enums\IdentifierType::Imo) }}</td>
                    <th scope="row">{{ __('operations::operations.fields.mmsi') }}</th>
                    <td>{{ $vessel->identifierValue(App\Enums\IdentifierType::Mmsi) }}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('operations::operations.fields.registration') }}</th>
                    <td>{{ $vessel->registration_number }}</td>
                    <th scope="row">{{ __('operations::operations.fields.call_sign') }}</th>
                    <td>{{ $vessel->call_sign ?? $vessel->identifierValue(App\Enums\IdentifierType::Ircs) }}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('operations::operations.fields.vessel_type') }}</th>
                    <td>{{ $vessel->vesselType?->label ?? ($vessel->type ? __($vessel->type->label()) : null) }}</td>
                    <th scope="row">{{ __('operations::operations.fields.length') }}</th>
                    <td>{{ $vessel->length_m ?? $vessel->length_meters }}</td>
                </tr>
                <tr>
                    <th scope="row">{{ __('operations::operations.fields.beam') }}</th>
                    <td>{{ $vessel->beam_m }}</td>
                    <th scope="row">{{ __('operations::operations.fields.tonnage') }}</th>
                    <td>{{ $vessel->gross_tonnage }}</td>
                </tr>
                @foreach (['owner' => $owner, 'local_agent' => $agent, 'captain' => $captain] as $role => $party)
                    <tr>
                        <th scope="row">{{ __('operations::operations.fields.'.$role) }}</th>
                        <td colspan="3">
                            @if ($party)
                                {{ trim(($party->first_name ?? '').' '.$party->name) }}
                                <div class="text-muted f-12">{{ collect([$party->address, $party->phone, $party->email])->filter()->join(' · ') }}</div>
                            @else
                                <span class="text-muted">{{ __('operations::operations.not_recorded') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach

            <tr>
                <th scope="row">{{ __('operations::operations.fields.vessel_position') }}</th>
                <td colspan="3" class="text-pre-line">{{ $context?->vessel_position }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.vessel_activity') }}</th>
                <td colspan="3" class="text-pre-line">{{ $context?->vessel_activity }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.comments') }}</th>
                <td colspan="3" class="text-pre-line">{{ $context?->comments }}</td>
            </tr>

            <tr><th colspan="4">{{ __('operations::operations.personnel_title') }}</th></tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.supervised_by') }}</th>
                <td colspan="3">{{ $operation->csp_manager_name ?? $manager?->fullName() }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.operator') }}</th>
                <td colspan="3">{{ $operator?->fullName() }}</td>
            </tr>
        </tbody>
    </table>
</div>
