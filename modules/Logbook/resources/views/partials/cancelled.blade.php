@if ($entry->isCancelled())
    <div class="text-danger f-12">{{ __('ui.deletion.cancelled', ['name' => $entry->canceller?->fullName() ?? '—', 'date' => $entry->cancelled_at->isoFormat('L LT'), 'reason' => $entry->cancellation_reason]) }}</div>
@endif
