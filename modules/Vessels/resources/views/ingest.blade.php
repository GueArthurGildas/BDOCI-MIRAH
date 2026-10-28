<x-layouts.app :title="__('vessels::vessels.ingest.title')">
    <x-ui.page-header :title="__('vessels::vessels.ingest.title')" :subtitle="__('vessels::vessels.ingest.subtitle')" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('vessels::vessels.ingest.title')">
            <form method="POST" action="{{ route('vessels.ingest.store') }}">
                @csrf
                <x-ui.form.select name="source" :label="__('vessels::vessels.ingest.fields.source')" :options="App\Enums\SourceSystem::options()" required />
                <x-ui.form.input name="external_id" :label="__('vessels::vessels.ingest.fields.external_id')" />
                <x-ui.form.input name="name" :label="__('vessels::vessels.ingest.fields.name')" required />
                <x-ui.form.select name="flag" :label="__('vessels::vessels.ingest.fields.flag')" :options="App\Support\Localization\Countries::options()" />
                <x-ui.form.input name="imo" :label="__('vessels::vessels.ingest.fields.imo')" />
                <x-ui.form.input name="mmsi" :label="__('vessels::vessels.ingest.fields.mmsi')" />
                <x-ui.form.input name="ircs" :label="__('vessels::vessels.ingest.fields.ircs')" />
                <x-ui.form.input name="registration" :label="__('vessels::vessels.ingest.fields.registration')" />
                <x-ui.form.select name="fleet_segment" :label="__('vessels::vessels.ingest.fields.fleet_segment')" :options="App\Enums\FleetSegment::options()" />
                <x-ui.form.input name="length_m" type="number" step="0.01" :label="__('vessels::vessels.ingest.fields.length_m')" />
                <x-ui.form.input name="gross_tonnage" type="number" step="0.01" :label="__('vessels::vessels.ingest.fields.gross_tonnage')" />
                <x-ui.form.actions :cancel="route('vessels.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
