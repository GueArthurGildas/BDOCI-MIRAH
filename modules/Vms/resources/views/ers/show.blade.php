<x-layouts.app :title="__('vms::vms.ers.detail_title')">
    <x-ui.page-header :title="__('vms::vms.ers.detail_title')" :breadcrumbs="[__('vms::vms.ers.title') => route('vms.ers.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('vms::vms.ers.detail_title')" :subtitle="$declaration->vessel->name.' · '.$declaration->declared_at->isoFormat('L LT')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('vms::vms.ers.species') }}</th>
                    <th>{{ __('vms::vms.ers.presentation') }}</th>
                    <th>{{ __('vms::vms.ers.conversion_factor') }}</th>
                    <th>{{ __('vms::vms.ers.live_weight_kg') }}</th>
                    <th>{{ __('vms::vms.ers.fao_zone') }}</th>
                    <th>{{ __('vms::vms.ers.gear') }}</th>
                </x-slot:head>
                @forelse ($declaration->lines as $line)
                    <tr>
                        <td>{{ $line->species->name }}</td>
                        <td>{{ $line->presentation?->label }}</td>
                        <td>{{ $line->conversion_factor }}</td>
                        <td>{{ Illuminate\Support\Number::format($line->live_weight_kg, 2) }}</td>
                        <td>{{ $line->fao_zone }}</td>
                        <td>{{ $line->gear?->label }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
