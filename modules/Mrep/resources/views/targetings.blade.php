<x-layouts.app :title="__('mrep::mrep.targetings.title')">
    <x-ui.page-header :title="__('mrep::mrep.targetings.title')" :subtitle="__('mrep::mrep.targetings.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('mrep::mrep.targetings.title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('mrep::mrep.fields.year') }}</th>
                    <th>{{ __('mrep::mrep.fields.target') }}</th>
                    <th class="text-end">{{ __('mrep::mrep.fields.objective_pct') }}</th>
                    <th class="text-end">{{ __('mrep::mrep.fields.achieved_pct') }}</th>
                </x-slot:head>
                @forelse ($targetings as $targeting)
                    <tr>
                        <td>{{ $targeting->year }}</td>
                        <td>{{ $targeting->target }}</td>
                        <td class="text-end">{{ $targeting->objective_pct }} %</td>
                        <td class="text-end">{{ $targeting->achieved_pct !== null ? $targeting->achieved_pct.' %' : '—' }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
