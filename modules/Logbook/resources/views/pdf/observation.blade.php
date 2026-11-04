<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('logbook::logbook.pdf.observation_title', ['id' => $observation->id]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; }
        th { width: 30%; background: #ddd; }
        .foot { font-size: 9px; color: #555; }
    </style>
</head>
<body>
    <h1>{{ __('logbook::logbook.pdf.observation_title', ['id' => $observation->id]) }}</h1>
    <table>
        <tr><th>{{ __('logbook::logbook.fields.observed_at') }}</th><td>{{ $observation->observed_at->isoFormat('L LT') }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.vessel') }}</th><td>{{ $observation->vessel?->name ?? '—' }}@if ($observation->vessel) ({{ $observation->vessel->registration_number }})@endif</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.zone') }}</th><td>{{ $observation->zone?->name ?? '—' }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.latitude') }} / {{ __('logbook::logbook.fields.longitude') }}</th><td>{{ $observation->latitude !== null && $observation->longitude !== null ? $observation->latitude.', '.$observation->longitude : '—' }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.level') }}</th><td>{{ __($observation->level->label()) }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.status') }}</th><td>{{ __($observation->status->label()) }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.description') }}</th><td>{!! nl2br(e($observation->description)) !!}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.follow_up') }}</th><td>{!! nl2br(e((string) $observation->follow_up)) ?: '—' !!}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.author') }}</th><td>{{ $observation->author?->fullName() ?? '—' }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.team') }}</th><td>{{ $observation->team?->name ?? '—' }}</td></tr>
        <tr><th>{{ __('logbook::logbook.fields.closed_at') }}</th><td>{{ $observation->closed_at?->isoFormat('L LT') ?? '—' }}</td></tr>
    </table>
    <p class="foot">{{ __('logbook::logbook.pdf.generated', ['date' => now()->isoFormat('L LT')]) }}</p>
</body>
</html>
