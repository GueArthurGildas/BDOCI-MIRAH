@props(['label'])

<tr>
    <th scope="row" class="w-50">{{ $label }}</th>
    <td>{{ $slot->isEmpty() ? '—' : $slot }}</td>
</tr>
