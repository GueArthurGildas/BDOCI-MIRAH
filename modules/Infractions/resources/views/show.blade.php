<x-layouts.app :title="$infraction->reference">
    <x-ui.page-header :title="$infraction->reference" :subtitle="$infraction->type->name" :breadcrumbs="[__('infractions::infractions.title') => route('infractions.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="__('infractions::infractions.fields.facts')">
                    <p class="m-b-20 text-pre-line">{{ $infraction->facts }}</p>

                    <x-ui.detail-list>
                        <x-ui.detail :label="__('infractions::infractions.fields.vessel')"><x-ui.vessel-link :vessel="$infraction->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.detected_at')">{{ $infraction->detected_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.type')">{{ $infraction->type->code }} — {{ $infraction->type->name }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.legal_basis')">{{ $infraction->type->legal_basis }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.inspection')">{{ $infraction->inspection?->external_reference }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.evidence')">{{ $infraction->evidence }}</x-ui.detail>
                        <x-ui.detail :label="__('infractions::infractions.fields.status')"><x-ui.status-badge :status="$infraction->status" /></x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>
            </div>

            <div class="col-xl-5">
                <x-ui.card :title="__('infractions::infractions.follow_up_title')">
                    @can(App\Support\Authorization\Permission::InfractionsManage->value)
                        <form method="POST" action="{{ route('infractions.update', $infraction) }}">
                            @csrf
                            @method('PATCH')
                            <x-ui.form.select name="status" :label="__('infractions::infractions.fields.status')" :options="App\Enums\InfractionStatus::options()" :value="$infraction->status" required />
                            <x-ui.form.textarea name="decision" :label="__('infractions::infractions.fields.decision')" :value="$infraction->decision" rows="3" />
                            <x-ui.form.input name="sanction" :label="__('infractions::infractions.fields.sanction')" :value="$infraction->sanction" />
                            <x-ui.form.actions :cancel="route('infractions.index')" />
                        </form>
                    @else
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('infractions::infractions.fields.decision')">{{ $infraction->decision }}</x-ui.detail>
                            <x-ui.detail :label="__('infractions::infractions.fields.sanction')">{{ $infraction->sanction }}</x-ui.detail>
                        </x-ui.detail-list>
                    @endcan
                </x-ui.card>

                <x-ui.audit-history :subject="$infraction" />
            </div>
        </div>
    </div>
</x-layouts.app>
