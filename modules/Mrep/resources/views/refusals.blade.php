<x-layouts.app :title="__('mrep::mrep.refusals.title')">
    <x-ui.page-header :title="__('mrep::mrep.refusals.title')" :subtitle="__('mrep::mrep.refusals.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('mrep::mrep.refusals.title')" :subtitle="trans_choice('ui.results_count', $refusals->total())" flush>
            <x-ui.table :paginator="$refusals">
                <x-slot:head>
                    <th>{{ __('mrep::mrep.fields.reference') }}</th>
                    <th>{{ __('mrep::mrep.fields.vessel') }}</th>
                    <th>{{ __('mrep::mrep.fields.refused_on') }}</th>
                </x-slot:head>
                @forelse ($refusals as $refusal)
                    <tr>
                        <td class="f-w-600">{{ $refusal->reference ?? '—' }}</td>
                        <td><x-ui.vessel-link :vessel="$refusal->vessel" /></td>
                        <td>{{ $refusal->refused_on?->isoFormat('L') ?? '—' }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="3" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
