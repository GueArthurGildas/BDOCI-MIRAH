<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('infractions::pdf.decision.title', ['reference' => $case->reference]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px; text-align: left; vertical-align: top; }
        th { background: #ddd; width: 30%; }
    </style>
</head>
<body>
    <h1>{{ __('infractions::pdf.decision.title', ['reference' => $case->reference]) }}</h1>
    <table>
        <tr><th>{{ __('infractions::infractions.fields.vessel') }}</th><td>{{ $case->vessel->name }}</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.follow_up_type') }}</th><td>{{ __($followUp->type->label()) }}</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.decision') }}</th><td>{{ $decision->label }}</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.is_penalty') }}</th><td>{{ $decision->is_penalty ? __('ui.yes') : __('ui.no') }}</td></tr>
        <tr><th>{{ __('infractions::infractions.fields.decided_by') }}</th><td>{{ $decider->name }}</td></tr>
        <tr><th>{{ __('infractions::pdf.decision.date') }}</th><td>{{ $decision->decided_at->format('d/m/Y H:i') }}</td></tr>
    </table>
</body>
</html>
