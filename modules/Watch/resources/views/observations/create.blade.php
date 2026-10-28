<x-layouts.app :title="__('watch::watch.observations.create_title')">
    <x-ui.page-header :title="__('watch::watch.observations.create_title')" :breadcrumbs="[__('watch::watch.observations.title') => route('watch.observations.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('watch::watch.observations.create_title')" :subtitle="__('watch::watch.observations.create_hint')">
            <form method="POST" action="{{ route('watch.observations.store') }}">
                @csrf
                <x-ui.form.select name="vessel_id" :label="__('watch::watch.fields.vessel')" :options="$vessels" required />
                <x-ui.form.textarea name="body" :label="__('watch::watch.fields.body')" required />
                <x-ui.form.select name="relay_to_team_id" :label="__('watch::watch.fields.relay_to')" :options="$teams" :placeholder="__('watch::watch.observations.no_relay')" />
                <x-ui.form.actions :cancel="route('watch.observations.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
