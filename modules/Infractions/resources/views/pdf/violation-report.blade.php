<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('infractions::pdf.violation_report.title', ['number' => $number]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        h2 { font-size: 12px; margin: 14px 0 4px; border-bottom: 1px solid #000; }
        p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px; text-align: left; vertical-align: top; }
        th { background: #ddd; }
    </style>
</head>
<body>
    <h1>{{ __('infractions::pdf.violation_report.title', ['number' => $number]) }}</h1>
    <p>{{ __('infractions::pdf.case_reference', ['reference' => $case->reference, 'date' => $case->opened_at->format('d/m/Y H:i')]) }}</p>

    <h2>{{ __('infractions::pdf.vessel') }}</h2>
    <table>
        <tr><th>{{ __('infractions::infractions.fields.vessel') }}</th><td>{{ $case->vessel->name }} ({{ $case->vessel->registration_number }})</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.captain') }}</th><td>{{ $case->captain?->full_name }}</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.shipowner') }}</th><td>{{ $case->shipowner?->name }}</td></tr>
    </table>

    <h2>{{ __('infractions::infractions.fields.facts') }}</h2>
    <p>{{ $case->facts }}</p>

    <h2>{{ __('infractions::infractions.sections.upheld') }}</h2>
    <table>
        <tr><th>{{ __('infractions::infractions.fields.code') }}</th><th>{{ __('infractions::infractions.fields.label') }}</th><th>{{ __('infractions::infractions.fields.legal_basis') }}</th></tr>
        @foreach ($case->upheld as $upheld)
            <tr><td>{{ $upheld->type->code }}</td><td>{{ $upheld->type->label }}</td><td>{{ $upheld->type->legal_basis }}</td></tr>
        @endforeach
    </table>

    <h2>{{ __('infractions::infractions.sections.evidence') }}</h2>
    <ul>
        @foreach ($case->evidence as $item)
            <li>{{ $item->description }}</li>
        @endforeach
    </ul>

    <h2>{{ __('infractions::infractions.sections.seals') }} / {{ __('infractions::infractions.sections.seizures') }} / {{ __('infractions::infractions.sections.measures') }}</h2>
    <ul>
        @foreach ($case->seals as $seal)
            <li>{{ __('infractions::pdf.seal', ['number' => $seal->number]) }}</li>
        @endforeach
        @foreach ($case->seizures as $seizure)
            <li>{{ $seizure->item }}</li>
        @endforeach
        @foreach ($case->measures as $measure)
            <li>{{ __($measure->type->label()) }}</li>
        @endforeach
    </ul>

    <h2>{{ __('infractions::infractions.fields.captain_observations') }}</h2>
    <p>{{ $case->captain_observations }}</p>

    <h2>{{ __('infractions::pdf.violation_report.notification') }}</h2>
    <p>
        {{ $notifiedAt ? __('infractions::pdf.violation_report.notified_at', ['date' => $notifiedAt->format('d/m/Y H:i')]) : __('infractions::pdf.violation_report.not_notified') }}
        @if ($isSignatureRefused)
            {{ __('infractions::pdf.violation_report.signature_refused') }}
        @endif
    </p>
</body>
</html>
