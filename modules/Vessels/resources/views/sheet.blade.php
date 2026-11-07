<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('vessels::vessels.sheet.title') }} — {{ $vessel->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1d2030; }
        h1 { margin: 0; font-size: 20px; }
        h2 { margin: 18px 0 6px; padding-bottom: 3px; border-bottom: 2px solid #f58f35; font-size: 12px; text-transform: uppercase; color: #c4640f; }
        .muted { color: #6b7280; }
        .head { width: 100%; }
        .head td { vertical-align: top; }
        .photo { width: 170px; height: 120px; object-fit: cover; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { padding: 4px 6px; border-bottom: 1px solid #e3e6ee; text-align: left; }
        table.data th { width: 32%; font-weight: normal; color: #6b7280; }
        .tag { display: inline-block; padding: 1px 6px; border: 1px solid #f58f35; border-radius: 3px; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 8px; color: #6b7280; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td>
                <h1>{{ $vessel->name }}</h1>
                <p class="muted">{{ $vessel->registration_number }} · {{ $vessel->flagName() }}</p>
                <p><span class="tag">{{ __($assessment->level->label()) }}</span></p>
                @foreach ($assessment->reasons as $reason)
                    <div class="muted">• {{ __('vigilance.reasons.'.$reason['key'], $reason['params']) }}</div>
                @endforeach
            </td>
            @if ($photo)
                <td style="width: 180px; text-align: right;"><img class="photo" src="{{ $photo }}" alt=""></td>
            @endif
        </tr>
    </table>

    <h2>{{ __('vessels::vessels.sheet.identification') }}</h2>
    <table class="data">
        <tr><th>{{ __('vessels::vessels.fields.registration_number') }}</th><td>{{ $vessel->registration_number ?: '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.imo_number') }}</th><td>{{ $vessel->imo_number ?: '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.header.mmsi') }}</th><td>{{ $vessel->identifierValue(App\Enums\IdentifierType::Mmsi) ?: '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.call_sign') }}</th><td>{{ $vessel->call_sign ?: '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.flag') }}</th><td>{{ $vessel->flagName() ?: '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.category') }}</th><td>{{ __($vessel->category->label()) }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.type') }}</th><td>{{ __($vessel->type->label()) }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.status') }}</th><td>{{ __($vessel->status->label()) }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.owner') }}</th><td>{{ $vessel->owner ?: '—' }}</td></tr>
        @foreach ([App\Enums\ActorRole::LocalAgent, App\Enums\ActorRole::Captain] as $role)
            <tr><th>{{ __($role->label()) }}</th><td>{{ $vessel->actors->first(fn ($actor) => $actor->role === $role && $actor->ended_on === null)?->holderName() ?: '—' }}</td></tr>
        @endforeach
    </table>

    <h2>{{ __('vessels::vessels.sheet.characteristics') }}</h2>
    <table class="data">
        <tr><th>{{ __('vessels::vessels.fields.length_meters') }}</th><td>{{ $vessel->length_meters ?? '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.gross_tonnage') }}</th><td>{{ $vessel->gross_tonnage ?? '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.construction_year') }}</th><td>{{ $vessel->construction_year ?? '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.home_port_id') }}</th><td>{{ $vessel->homePort?->name ?? '—' }}</td></tr>
        <tr><th>{{ __('vessels::vessels.fields.propulsion') }}</th><td>{{ $vessel->propulsion ?: '—' }}</td></tr>
    </table>

    <h2>{{ __('vessels::vessels.sheet.license') }}</h2>
    <table class="data">
        @if ($vessel->currentLicense)
            <tr><th>{{ __('vessels::vessels.columns.number') }}</th><td>{{ $vessel->currentLicense->number }}</td></tr>
            <tr><th>{{ __('vessels::vessels.fields.license') }}</th><td>{{ __($vessel->currentLicense->standing->label()) }}</td></tr>
            <tr><th>{{ __('vessels::vessels.sheet.expires_on') }}</th><td>{{ $vessel->currentLicense->expires_on->isoFormat('L') }}</td></tr>
        @else
            <tr><td colspan="2" class="muted">{{ __('vessels::vessels.summary.no_license') }}</td></tr>
        @endif
    </table>

    <h2>{{ __('vessels::vessels.sheet.tracking') }}</h2>
    <table class="data">
        <tr><th>{{ __('vessels::vessels.fields.beacon') }}</th><td>{{ __($vessel->beaconStatus()->label()) }}</td></tr>
        @foreach ($vessel->trackingDevices->whereNull('removed_at') as $device)
            <tr><th>{{ __($device->system->label()) }}</th><td>{{ $device->identifier }}</td></tr>
        @endforeach
        <tr>
            <th>{{ __('vessels::vessels.fields.last_emission') }}</th>
            <td>
                @if ($vessel->latestPosition)
                    {{ $vessel->latestPosition->recorded_at->isoFormat('L LT') }} ({{ App\Support\Vms\EmissionGap::format($vessel->latestPosition->recorded_at) }})
                    @if ($vessel->latestPosition->zone) — {{ $vessel->latestPosition->zone->name }} @endif
                @else
                    {{ __('vessels::vessels.summary.never_emitted') }}
                @endif
            </td>
        </tr>
    </table>

    <p class="footer">{{ __('vessels::vessels.sheet.generated', ['date' => $generatedAt->isoFormat('L LT'), 'user' => $generatedBy->name]) }}</p>
</body>
</html>
