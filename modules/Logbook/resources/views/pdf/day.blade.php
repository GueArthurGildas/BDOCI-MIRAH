<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('logbook::logbook.pdf.title', ['day' => $day->day->format('d/m/Y')]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        h1 { font-size: 15px; margin: 0 0 4px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #ddd; }
        .open { color: #b00000; }
        .done { color: #006400; }
    </style>
</head>
<body>
    <h1>{{ __('logbook::logbook.pdf.title', ['day' => $day->day->format('d/m/Y')]) }}</h1>
    <p>{{ __('logbook::logbook.days.shift_range', ['from' => $day->shift_start_at->format('H:i'), 'to' => $day->shift_end_at->format('H:i')]) }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('logbook::logbook.pdf.when') }}</th>
                <th>{{ __('logbook::logbook.pdf.how') }}</th>
                <th>{{ __('logbook::logbook.pdf.what') }}</th>
                <th>{{ __('logbook::logbook.entries.fields.handling_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($day->entries as $entry)
                <tr>
                    <td>{{ $entry->occurred_at->format('H:i') }} UTC</td>
                    <td>{{ $entry->channel->label }} ({{ __($entry->direction->label()) }})</td>
                    <td>
                        {{ $entry->content }}
                        @if ($entry->is_order_or_decision)
                            <br><strong>{{ __('logbook::logbook.entries.order_or_decision') }}</strong>
                        @endif
                        @if ($entry->vessels->isNotEmpty())
                            <br>{{ $entry->vessels->pluck('name')->implode(', ') }}
                        @endif
                        @if ($entry->operation_id && $operations->has($entry->operation_id))
                            <br>{{ $operations[$entry->operation_id]->code }}
                        @endif
                        @if ($entry->corrects_entry_id)
                            <br><em>{{ __('logbook::logbook.pdf.rectifying_line') }}</em>
                        @endif
                    </td>
                    <td class="{{ $entry->handling_status === App\Enums\LogbookHandlingStatus::Closed ? 'done' : 'open' }}">{{ __($entry->handling_status->label()) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        {{ __('logbook::logbook.days.counters.entries_total') }} : {{ $counters['entries_total'] }} ·
        {{ __('logbook::logbook.days.counters.received') }} : {{ $counters['received'] }} ·
        {{ __('logbook::logbook.days.counters.sent') }} : {{ $counters['sent'] }} ·
        {{ __('logbook::logbook.days.counters.orders_or_decisions') }} : {{ $counters['orders_or_decisions'] }} ·
        {{ __('logbook::logbook.days.counters.corrections') }} : {{ $counters['corrections'] }}
    </p>
</body>
</html>
