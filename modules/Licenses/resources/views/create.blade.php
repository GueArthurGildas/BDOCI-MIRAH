<x-layouts.app :title="__('licenses::licenses.create_title')">
    <x-ui.page-header :title="__('licenses::licenses.create_title')" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('licenses::licenses.create_title')">
            <form method="POST" action="{{ route('licenses.store') }}">
                @csrf
                <x-ui.form.select name="vessel_id" :label="__('licenses::licenses.fields.vessel')" :options="$vessels" required />
                <x-ui.form.input name="number" :label="__('licenses::licenses.fields.number')" maxlength="255" required />
                <x-ui.form.select name="type" :label="__('licenses::licenses.fields.type')" :options="App\Enums\LicenseType::options()" required />
                <x-ui.form.input name="valid_from" type="date" :label="__('licenses::licenses.fields.valid_from')" required />
                <x-ui.form.input name="valid_until" type="date" :label="__('licenses::licenses.fields.valid_until')" required />
                <x-ui.form.actions :cancel="route('licenses.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
