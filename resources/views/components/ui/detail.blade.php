@props(['label'])

<tr>
    <th scope="row" class="w-50">{{ $label }}</th>
    <td>{{ $slot->hasActualContent() ? $slot : '—' }}</td>
</tr>
