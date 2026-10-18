<x-layouts.app :title="__('movements::movements.create_title')">
    <x-ui.page-header :title="__('movements::movements.create_title')" :breadcrumbs="[__('movements::movements.title') => route('movements.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('movements::movements.create_title')">
            <form method="POST" action="{{ route('movements.store') }}">
                @csrf
                <x-ui.form.select name="vessel_id" :label="__('movements::movements.fields.vessel')" :options="$vessels" required />
                <x-ui.form.select name="direction" :label="__('movements::movements.fields.direction')" :options="App\Enums\MovementDirection::options()" required />
                <x-ui.form.select name="port_id" :label="__('movements::movements.fields.port')" :options="$ports" required />
                <x-ui.form.input name="occurred_at" type="datetime-local" :label="__('movements::movements.fields.occurred_at')" :value="now()->format('Y-m-d\TH:i')" required />
                <x-ui.form.input name="reason" :label="__('movements::movements.fields.reason')" />
                <x-ui.form.textarea name="notes" :label="__('movements::movements.fields.notes')" rows="3" />
                <x-ui.form.actions :cancel="route('movements.index')" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
