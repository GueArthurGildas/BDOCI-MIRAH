<x-layouts.app :title="__('vms::vms.ers.new')">
    <x-ui.page-header :title="__('vms::vms.ers.new')" :breadcrumbs="[__('vms::vms.ers.title') => route('vms.ers.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('vms::vms.ers.new')">
            <form method="POST" action="{{ route('vms.ers.store') }}">
                @csrf
                <x-ui.form.select name="vessel_id" :label="__('vms::vms.fields.vessel')" :options="$vessels" required />
                <x-ui.form.input name="declared_at" type="datetime-local" :label="__('vms::vms.ers.declared_at')" />
                <x-ui.form.select name="lines[0][species_id]" :label="__('vms::vms.ers.species')" :options="$species" required />
                <x-ui.form.select name="lines[0][presentation_id]" :label="__('vms::vms.ers.presentation')" :options="$presentations" />
                <x-ui.form.input name="lines[0][live_weight_kg]" type="number" step="0.01" min="0" :label="__('vms::vms.ers.live_weight_kg')" required />
                <x-ui.form.input name="lines[0][fao_zone]" :label="__('vms::vms.ers.fao_zone')" />
                <x-ui.form.select name="lines[0][gear_id]" :label="__('vms::vms.ers.gear')" :options="$gears" />
                <x-ui.form.actions :cancel="route('vms.ers.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
