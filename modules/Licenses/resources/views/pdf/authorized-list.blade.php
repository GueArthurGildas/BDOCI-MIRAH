<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('licenses::authorized.pdf.title', ['version' => $version]) }}</title>
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
    <h1>{{ __('licenses::authorized.pdf.title', ['version' => $version]) }}</h1>
    <p>{{ __('licenses::authorized.pdf.published_at', ['date' => $publishedAt->format('d/m/Y H:i'), 'count' => $rows->count()]) }}</p>
    <table>
        <thead>
            <tr>
                <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                <th>{{ __('licenses::licenses.fields.number') }}</th>
                <th>{{ __('licenses::licenses.fields.segment') }}</th>
                <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                <th>{{ __('licenses::licenses.fields.zones') }}</th>
                <th>{{ __('licenses::licenses.fields.gears') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $license)
                <tr>
                    <td>{{ $license->vessel->name }}</td>
                    <td>{{ $license->number }}</td>
                    <td>{{ $license->segment ? __($license->segment->label()) : '' }}</td>
                    <td>{{ $license->expires_on->format('d/m/Y') }}</td>
                    <td>{{ $license->zones->pluck('name')->join(', ') }}</td>
                    <td>{{ $license->gears->pluck('label')->join(', ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
