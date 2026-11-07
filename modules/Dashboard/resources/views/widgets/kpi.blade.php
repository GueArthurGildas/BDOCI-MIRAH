<a href="{{ route('dashboard.widgets.show', $data->widget->key) }}" data-gs-modal class="gs-widget-link" data-bs-toggle="tooltip" data-bs-title="{{ $data->widget->explanation() }}">
    <x-ui.stat-card
        :label="$data->widget->title()"
        :value="Number::format($data->value)"
        :icon="$data->widget->icon"
        :color="$data->widget->color"
        :footer="__('ui.updated_at', ['time' => $now->isoFormat('LT')])"
    />
</a>
