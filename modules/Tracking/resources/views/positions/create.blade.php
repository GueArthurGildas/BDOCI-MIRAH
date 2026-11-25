<x-layouts.app :title="__('tracking::tracking.manual.title')">
    <x-ui.page-header :title="__('tracking::tracking.manual.title')" :subtitle="__('tracking::tracking.manual.subtitle')" :breadcrumbs="$slot ? [__('tracking::tracking.slots.title') => route('tracking.slots.index'), __('tracking::tracking.slots.detail_title') => route('tracking.slots.show', $slot)] : [__('tracking::tracking.zones.title') => route('tracking.zones')]" />

    <div class="page-body">
        <x-ui.card :title="__('tracking::tracking.manual.title')" :subtitle="__('tracking::tracking.manual.hint')">
            <form method="POST" action="{{ route('tracking.positions.store') }}">
                @csrf
                @if ($slot)
                    <input type="hidden" name="control_slot_id" value="{{ $slot->id }}">
                @endif
                <x-ui.form.select name="vessel_id" :label="__('tracking::tracking.manual.fields.vessel_id')" :options="$vessels" :value="$selectedVesselId" required />
                <x-ui.form.input name="recorded_at" type="datetime-local" :label="__('tracking::tracking.manual.fields.recorded_at')" :value="now()->format('Y-m-d\TH:i')" required />
                <x-ui.form.select name="zone_id" :label="__('tracking::tracking.manual.fields.zone_id')" :options="$zones" />
                <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('tracking::tracking.manual.fields.latitude')" />
                <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('tracking::tracking.manual.fields.longitude')" />
                <x-ui.form.input name="speed_knots" type="number" step="0.1" min="0" :label="__('tracking::tracking.manual.fields.speed_knots')" />
                <x-ui.form.select name="activity" :label="__('tracking::tracking.manual.fields.activity')" :options="$activities" />
                <x-ui.form.actions :cancel="$slot ? route('tracking.slots.show', $slot) : route('tracking.zones')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
