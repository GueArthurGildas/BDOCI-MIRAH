<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('licenses::register.pdf.title', ['year' => $year]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        h1 { font-size: 15px; margin: 0 0 4px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #ddd; }
    </style>
</head>
<body>
    <h1>{{ __('licenses::register.pdf.title', ['year' => $year]) }}</h1>
    <p>{{ __('licenses::register.pdf.count', ['count' => $licenses->count()]) }}</p>
    <table>
        <thead>
            <tr>
                <th>{{ __('licenses::licenses.fields.number') }}</th>
                <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                <th>{{ __('licenses::licenses.fields.segment') }}</th>
                <th>{{ __('licenses::licenses.fields.issued_on') }}</th>
                <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                <th>{{ __('licenses::licenses.fields.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($licenses as $license)
                <tr>
                    <td>{{ $license->number }}</td>
                    <td>{{ $license->vessel->name }}</td>
                    <td>{{ $license->segment ? __($license->segment->label()) : '' }}</td>
                    <td>{{ $license->issued_on?->format('d/m/Y') }}</td>
                    <td>{{ $license->expires_on->format('d/m/Y') }}</td>
                    <td>{{ __($license->standing->label()) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
