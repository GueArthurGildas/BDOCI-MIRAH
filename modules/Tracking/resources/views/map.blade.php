<x-layouts.app :title="__('tracking::tracking.map.title')">
    <x-ui.page-header :title="__('tracking::tracking.map.title')" :subtitle="__('tracking::tracking.map.subtitle')" />

    <div class="page-body">
        <x-ui.card data-guide="map" :title="trans_choice('tracking::tracking.map.positions_count', $positionsCount)">
            <x-slot:actions>
                <form method="GET" action="{{ route('tracking.map') }}" class="d-flex gap-2">
                    <select name="category" class="form-select form-select-sm" data-auto-submit aria-label="{{ __('tracking::tracking.map.category') }}">
                        <option value="">{{ __('ui.all') }}</option>
                        @foreach (App\Enums\VesselCategory::options() as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['category'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </x-slot:actions>

            <x-ui.map :markers="$markers" :zones="$zones" height="620px" />

            <div class="d-flex flex-wrap gap-4 m-t-15 f-13">
                @foreach (Modules\Tracking\Http\Controllers\TrackingController::MARKER_COLORS as $kind => $color)
                    <span><i class="fas fa-circle m-r-5" style="color: {{ $color }}"></i>{{ __('tracking::tracking.map.legend.'.$kind) }}</span>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
