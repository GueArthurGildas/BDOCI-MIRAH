@php
    $number = fn (string $code) => explode('-', $code)[1] ?? $code;
    $opened = $counters['operations_opened'] ?? [];
    $closed = $counters['operations_closed'] ?? [];
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
        <thead>
            <tr><th colspan="4">{{ __('logbook::logbook.days.closing_block') }}</th></tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">{{ __('logbook::logbook.handovers.fields.date') }}</th>
                <td>{{ $closedAt?->format('d/m/Y') }}</td>
                <th scope="row">{{ __('logbook::logbook.handovers.fields.time') }}</th>
                <td>{{ $closedAt?->format('H:i') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('logbook::logbook.handovers.fields.outgoing') }}</th>
                <td colspan="3">{{ $closer?->fullName() }}</td>
            </tr>
            <tr><th colspan="4">{{ __('logbook::logbook.days.counters.operation_counters') }}</th></tr>
            @foreach (collect($counters['operation_counters'] ?? [])->chunk(2) as $pair)
                <tr>
                    @foreach ($pair as $code => $last)
                        <th scope="row">{{ strtoupper($code) }}</th>
                        <td>{{ str_pad((string) $last, 3, '0', STR_PAD_LEFT) }}</td>
                    @endforeach
                    @if ($pair->count() === 1)
                        <td colspan="2"></td>
                    @endif
                </tr>
            @endforeach
            <tr><th colspan="4">{{ __('logbook::logbook.days.counters.open_operations') }}</th></tr>
            @foreach ($counters['open_operations'] ?? [] as $code => $codes)
                <tr>
                    <th scope="row">{{ strtoupper($code) }}</th>
                    <td colspan="3">
                        @forelse ($codes as $operationCode)
                            <span title="{{ $operationCode }}">{{ $number($operationCode) }}</span>@unless ($loop->last) / @endunless
                        @empty
                            {{ __('logbook::logbook.days.counters.none') }}
                        @endforelse
                    </td>
                </tr>
            @endforeach
            <tr><th colspan="4">{{ __('logbook::logbook.handovers.fields.instructions') }}</th></tr>
            <tr><td colspan="4" class="text-pre-line">{{ $instructions }}</td></tr>
            <tr>
                <th colspan="2">{{ __('logbook::logbook.days.counters.operations_opened') }}</th>
                <th colspan="2">{{ __('logbook::logbook.days.counters.operations_closed') }}</th>
            </tr>
            @foreach ($opened as $code => $count)
                <tr>
                    <th scope="row">{{ strtoupper($code) }}</th>
                    <td>{{ $count }}</td>
                    <th scope="row">{{ strtoupper($code) }}</th>
                    <td>{{ $closed[$code] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
