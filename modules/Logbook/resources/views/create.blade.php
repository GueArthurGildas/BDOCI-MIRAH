<x-layouts.app :title="__('logbook::logbook.create_title')">
    <x-ui.page-header :title="__('logbook::logbook.create_title')" :breadcrumbs="[__('logbook::logbook.title') => route('logbook.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('logbook::logbook.create_title')">
            <form method="POST" action="{{ route('logbook.store') }}">
                @csrf
                <x-ui.form.input name="observed_at" type="datetime-local" :label="__('logbook::logbook.fields.observed_at')" :value="now()->format('Y-m-d\TH:i')" required />
                <x-ui.form.select name="vessel_id" :label="__('logbook::logbook.fields.vessel')" :options="$vessels" :placeholder="__('logbook::logbook.no_vessel')" />
                <x-ui.form.select name="level" :label="__('logbook::logbook.fields.level')" :options="App\Enums\ObservationLevel::options()" required />
                <x-ui.form.select name="zone_id" :label="__('logbook::logbook.fields.zone')" :options="$zones" />
                <x-ui.form.input name="latitude" type="number" step="0.000001" :label="__('logbook::logbook.fields.latitude')" />
                <x-ui.form.input name="longitude" type="number" step="0.000001" :label="__('logbook::logbook.fields.longitude')" />
                <x-ui.form.textarea name="description" :label="__('logbook::logbook.fields.description')" required />
                <x-ui.form.textarea name="follow_up" :label="__('logbook::logbook.fields.follow_up')" rows="3" />
                <x-ui.form.actions :cancel="route('logbook.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
