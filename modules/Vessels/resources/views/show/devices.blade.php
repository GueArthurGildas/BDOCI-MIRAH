<x-ui.card :title="__('vessels::vessels.tabs.devices')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.columns.system') }}</th>
            <th>{{ __('vessels::vessels.columns.identifier') }}</th>
            <th>{{ __('vessels::vessels.columns.installed_at') }}</th>
            <th>{{ __('vessels::vessels.columns.removed_at') }}</th>
        </x-slot:head>
        @forelse ($vessel->trackingDevices as $device)
            <tr @class(['text-muted' => ! $device->isInstalled()])>
                <td><x-ui.status-badge :status="$device->system" /></td>
                <td>{{ $device->identifier }}</td>
                <td>{{ $device->installed_at->isoFormat('L') }}</td>
                <td>{{ $device->removed_at?->isoFormat('L') ?? '—' }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="4" />
        @endforelse
    </x-ui.table>
</x-ui.card>
