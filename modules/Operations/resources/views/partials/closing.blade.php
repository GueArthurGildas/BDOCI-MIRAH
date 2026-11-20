@php
    $end = $operation->ended_at;
    $minutes = $end ? (int) $operation->started_at->diffInMinutes($end) : null;
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
        <tbody>
            <tr>
                <th scope="row" class="w-25">{{ __('operations::operations.fields.started_on') }}</th>
                <td>{{ $operation->started_at->format('d/m/Y H:i') }}</td>
                <th scope="row">{{ __('operations::operations.fields.ended_on') }}</th>
                <td>{{ $end?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.duration') }}</th>
                <td colspan="3">
                    @if ($minutes !== null)
                        {{ __('operations::operations.duration', ['days' => intdiv($minutes, 1440), 'hours' => intdiv($minutes % 1440, 60), 'minutes' => $minutes % 60]) }}
                    @endif
                </td>
            </tr>
            <tr><th colspan="4">{{ __('operations::operations.reminder_title') }}</th></tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.type') }}</th>
                <td>{{ strtoupper($operation->category->code->value) }}</td>
                <th scope="row">{{ __('operations::operations.fields.number') }}</th>
                <td>{{ str_pad((string) $operation->number, 3, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.vessel') }}</th>
                <td colspan="3">{{ $operation->vessels->pluck('name')->join(', ') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('operations::operations.fields.summary') }}</th>
                <td colspan="3" class="text-pre-line">{{ $operation->summary }}</td>
            </tr>
            <tr><th colspan="4">{{ __('operations::operations.fields.result') }}</th></tr>
            <tr><td colspan="4" class="text-pre-line">{{ $operation->result }}</td></tr>
            <tr><th colspan="4">{{ __('operations::operations.fields.infractions_noted') }}</th></tr>
            <tr><td colspan="4" class="text-pre-line">{{ $operation->infractions_noted }}</td></tr>
        </tbody>
    </table>
</div>
