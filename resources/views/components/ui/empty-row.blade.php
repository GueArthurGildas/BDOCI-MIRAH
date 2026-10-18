@props(['colspan'])

<tr>
    <td colspan="{{ $colspan }}" class="text-center text-muted p-4">
        {{ $slot->isEmpty() ? __('ui.no_results') : $slot }}
    </td>
</tr>
