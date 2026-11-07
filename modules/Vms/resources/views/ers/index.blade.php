<x-layouts.app :title="__('vms::vms.ers.title')">
    <x-ui.page-header :title="__('vms::vms.ers.title')" :subtitle="__('vms::vms.ers.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('vms::vms.ers.list_title')" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::ReportsExportErsDeclarations->value)
                    @foreach (['csv' => 'icon-file-text', 'pdf' => 'icon-file'] as $format => $icon)
                        <a href="{{ route('reports.exports.download', ['type' => 'ers_declarations', 'format' => $format, 'vessel' => $filters['vessel'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}" class="btn btn-dark btn-mini" title="{{ __('vms::vms.ers.export_hint') }}">
                            <i class="feather {{ $icon }}"></i> {{ __('vms::vms.ers.export_'.$format) }}
                        </a>
                    @endforeach
                @endcan
                @can(App\Support\Authorization\Permission::VmsCreate->value)
                    <a href="{{ route('vms.ers.create') }}" class="btn btn-primary btn-mini"><i class="feather icon-plus"></i> {{ __('vms::vms.ers.new') }}</a>
                @endcan
            </x-slot:actions>
            <x-ui.table-toolbar :action="route('vms.ers.index')" :paginator="$declarations">
                <x-ui.toolbar-select name="vessel" :label="__('vms::vms.fields.vessel')" :options="$vessels" :value="$filters['vessel'] ?? null" />
                <x-ui.toolbar-input name="from" type="date" :label="__('vms::vms.ers.from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input name="to" type="date" :label="__('vms::vms.ers.to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$declarations">
                <x-slot:head>
                    <th>{{ __('vms::vms.fields.vessel') }}</th>
                    <th>{{ __('vms::vms.ers.declared_at') }}</th>
                    <th>{{ __('vms::vms.ers.source') }}</th>
                    <th>{{ __('vms::vms.ers.lines') }}</th>
                    <th>{{ __('vms::vms.ers.total_weight') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($declarations as $declaration)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$declaration->vessel" /></td>
                        <td class="text-nowrap">{{ $declaration->declared_at->isoFormat('L LT') }}</td>
                        <td>{{ __($declaration->source->label()) }}</td>
                        <td>{{ $declaration->lines_count }}</td>
                        <td>{{ Illuminate\Support\Number::format((float) $declaration->lines_sum_live_weight_kg, 0) }}</td>
                        <td class="text-end"><a href="{{ route('vms.ers.show', $declaration) }}" class="btn btn-outline-primary btn-mini">{{ __('vms::vms.failures.open') }}</a></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
