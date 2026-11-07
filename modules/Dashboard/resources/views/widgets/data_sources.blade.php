<x-ui.card :title="$data->widget->title()" flush data-guide="sources">
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('dashboard::dashboard.columns.source') }}</th>
            <th>{{ __('dashboard::dashboard.columns.last_synced_at') }}</th>
            <th class="text-end">{{ __('dashboard::dashboard.columns.errors') }}</th>
            <th class="text-end">{{ __('dashboard::dashboard.columns.rejected') }}</th>
            <th>{{ __('dashboard::dashboard.columns.status') }}</th>
        </x-slot:head>
        @foreach ($data->records as $source)
            <tr>
                <td>{{ $source->name }}</td>
                <td>{{ $source->last_synced_at?->diffForHumans() ?? __('dashboard::dashboard.never') }}</td>
                <td class="text-end">{{ $source->open_errors_count }}</td>
                <td class="text-end">{{ $source->pending_rejections_count }}</td>
                <td><x-ui.status-badge :status="$source->indicator_status" /></td>
            </tr>
        @endforeach
    </x-ui.table>
</x-ui.card>
