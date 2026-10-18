@props(['colspan'])

<tr>
    <td colspan="{{ $colspan }}" class="text-center text-muted p-4">
        {{ $slot->hasActualContent() ? $slot : __('ui.no_results') }}
    </td>
</tr>
