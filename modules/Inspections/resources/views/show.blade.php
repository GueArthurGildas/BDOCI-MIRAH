<x-layouts.app :title="$inspection->external_reference">
    <x-ui.page-header :title="$inspection->external_reference" :subtitle="$inspection->inspected_at->isoFormat('LL')" :breadcrumbs="[__('inspections::inspections.title') => route('inspections.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('inspections::inspections.title')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('inspections::inspections.fields.vessel')"><x-ui.vessel-link :vessel="$inspection->vessel" /></x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.type')">{{ __($inspection->type->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.port')">{{ $inspection->port?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.inspectors')">{{ $inspection->inspectors }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.result')"><x-ui.status-badge :status="$inspection->result" /></x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('inspections::inspections.fields.findings')">
                    <p class="m-0 text-pre-line">{{ $inspection->findings ?? '—' }}</p>
                </x-ui.card>

                <x-ui.card :title="__('inspections::inspections.infractions_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('inspections::inspections.fields.reference') }}</th>
                            <th>{{ __('inspections::inspections.fields.infraction') }}</th>
                            <th>{{ __('inspections::inspections.fields.status') }}</th>
                        </x-slot:head>
                        @forelse ($inspection->infractions as $infraction)
                            <tr>
                                <td>{{ $infraction->reference }}</td>
                                <td class="text-wrap">{{ $infraction->type->name }}</td>
                                <td><x-ui.status-badge :status="$infraction->status" /></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
