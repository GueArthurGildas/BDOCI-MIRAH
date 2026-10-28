@php($title = $inspection->reference ?? $inspection->external_id)

<x-layouts.app :title="$title">
    <x-ui.page-header :title="$title" :subtitle="$inspection->started_at?->isoFormat('LL')" :breadcrumbs="[__('inspections::inspections.title') => route('inspections.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('inspections::inspections.title')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('inspections::inspections.fields.vessel')">
                            @if ($inspection->vessel)
                                <x-ui.vessel-link :vessel="$inspection->vessel" />
                            @else
                                <span class="label label-warning">{{ __('inspections::inspections.unmatched') }}</span>
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.control_type')">{{ $inspection->control_type ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.location_type')">{{ $inspection->location_type ? __($inspection->location_type->label()) : '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.port')">{{ $inspection->port?->name ?? '—' }}</x-ui.detail>
                        @if ($inspection->latitude !== null)
                            <x-ui.detail :label="__('inspections::inspections.fields.position')">{{ $inspection->latitude }}, {{ $inspection->longitude }}</x-ui.detail>
                        @endif
                        <x-ui.detail :label="__('inspections::inspections.fields.started_at')">{{ $inspection->started_at?->isoFormat('L LT') ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.ended_at')">{{ $inspection->ended_at?->isoFormat('L LT') ?? '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.result')">{{ $inspection->resultLabel() }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.captain_signature')">{{ $inspection->captain_signature_status ? __($inspection->captain_signature_status->label()) : '—' }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.copy_handed_over')">{{ $inspection->is_copy_handed_over === null ? '—' : __($inspection->is_copy_handed_over ? 'ui.yes' : 'ui.no') }}</x-ui.detail>
                        <x-ui.detail :label="__('inspections::inspections.fields.read_at')">{{ $inspection->read_at->isoFormat('L LT') }}</x-ui.detail>
                    </x-ui.detail-list>

                    @php($url = $link?->urlFor($inspection->external_id))
                    @if ($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-default btn-sm m-t-10"><i class="feather icon-external-link"></i> {{ __('inspections::inspections.open_in_source') }}</a>
                    @elseif ($link?->is_copy_fallback ?? true)
                        <button type="button" class="btn btn-default btn-sm m-t-10" onclick="navigator.clipboard.writeText(@js($inspection->external_id))"><i class="feather icon-copy"></i> {{ __('inspections::inspections.copy_identifier') }} ({{ $inspection->external_id }})</button>
                    @endif
                </x-ui.card>

                <x-ui.card :title="__('inspections::inspections.documents_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('inspections::inspections.fields.type') }}</th>
                            <th>{{ __('inspections::inspections.fields.compliant') }}</th>
                        </x-slot:head>
                        @forelse ($inspection->documents as $document)
                            <tr>
                                <td>{{ __($document->type->label()) }}</td>
                                <td>{{ $document->is_compliant === null ? '—' : __($document->is_compliant ? 'ui.yes' : 'ui.no') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('inspections::inspections.gears_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('inspections::inspections.fields.gear') }}</th>
                            <th class="text-end">{{ __('inspections::inspections.fields.mesh_mm') }}</th>
                            <th>{{ __('inspections::inspections.fields.marking') }}</th>
                            <th>{{ __('inspections::inspections.fields.compliant') }}</th>
                        </x-slot:head>
                        @forelse ($inspection->gears as $gear)
                            <tr>
                                <td>{{ $gear->gear?->label ?? '—' }}</td>
                                <td class="text-end">{{ $gear->mesh_mm ?? '—' }}</td>
                                <td>{{ $gear->is_marking_compliant === null ? '—' : __($gear->is_marking_compliant ? 'ui.yes' : 'ui.no') }}</td>
                                <td>{{ $gear->is_compliant === null ? '—' : __($gear->is_compliant ? 'ui.yes' : 'ui.no') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
            <div class="col-xl-6">
                <x-ui.card :title="__('inspections::inspections.catches_title')" :subtitle="__('inspections::inspections.threshold_hint', ['percent' => config('surveillance.connectors.catch_variance_threshold_percent')])" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('inspections::inspections.fields.species') }}</th>
                            <th>{{ __('inspections::inspections.fields.presentation') }}</th>
                            <th class="text-end">{{ __('inspections::inspections.fields.declared') }}</th>
                            <th class="text-end">{{ __('inspections::inspections.fields.observed') }}</th>
                            <th class="text-end">{{ __('inspections::inspections.fields.variance') }}</th>
                        </x-slot:head>
                        @forelse ($inspection->catches as $catch)
                            <tr>
                                <td>{{ $catch->species->name }}</td>
                                <td>{{ $catch->presentation?->label ?? '—' }}</td>
                                <td class="text-end">{{ $catch->declared_weight_kg !== null ? number_format($catch->declared_weight_kg, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end">{{ $catch->observed_weight_kg !== null ? number_format($catch->observed_weight_kg, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end">
                                    @if ($catch->variance_pct !== null)
                                        <span @class(['label', 'label-danger' => $catch->exceeds_csp_threshold, 'label-success' => ! $catch->exceeds_csp_threshold])>{{ $catch->variance_pct }} %</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('inspections::inspections.infractions_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('inspections::inspections.fields.provision') }}</th>
                            <th>{{ __('inspections::inspections.fields.stamp') }}</th>
                            <th>{{ __('inspections::inspections.fields.measures') }}</th>
                        </x-slot:head>
                        @forelse ($inspection->infractions as $infraction)
                            <tr>
                                <td class="text-wrap">{{ $infraction->provision }}</td>
                                <td>{{ $infraction->stamp ?? '—' }}</td>
                                <td class="text-wrap">{{ $infraction->measures ?? '—' }}</td>
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
