<x-ui.card :title="$data->widget->title()" :data-guide="['zones' => 'zones', 'infractions_by_status' => 'infractions'][$data->widget->key] ?? null">
    <x-slot:actions>
        <ul class="list-unstyled card-option">
            <li><a href="{{ route('dashboard.widgets.show', $data->widget->key) }}" data-gs-modal data-bs-toggle="tooltip" data-bs-title="{{ __('dashboard::dashboard.details.open') }}"><i class="feather icon-info"></i></a></li>
            <li><i class="feather icon-maximize full-card"></i></li>
            <li><i class="feather icon-minus minimize-card"></i></li>
        </ul>
    </x-slot:actions>
    <x-ui.chart :config="$data->chart" height="240" />
    @include('dashboard::widgets._figures', ['figures' => $data->figures])
</x-ui.card>
