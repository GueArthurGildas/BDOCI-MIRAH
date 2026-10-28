@php($externalLinks = App\Models\ExternalLink::all()->keyBy(fn ($link) => $link->source->value))
<x-ui.card :title="__('vessels::vessels.tabs.sources')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.sources.source') }}</th>
            <th>{{ __('vessels::vessels.sources.external_id') }}</th>
            <th>{{ __('vessels::vessels.sources.last_import_at') }}</th>
            <th>{{ __('vessels::vessels.sources.matching_status') }}</th>
            <th>{{ __('vessels::vessels.sources.criterion') }}</th>
            <th>{{ __('vessels::vessels.sources.link') }}</th>
        </x-slot:head>
        @forelse ($vessel->sources as $source)
            <tr>
                <td>{{ __($source->source->label()) }}</td>
                <td>{{ $source->external_id ?? '—' }}</td>
                <td>{{ $source->last_import_at?->isoFormat('L LT') ?? '—' }}</td>
                <td>{{ __('enums.matching_status.'.$source->matching_status->value) }}</td>
                <td>{{ $source->matching_criterion ? __($source->matching_criterion->label()) : '—' }}</td>
                <td>
                    @php($externalLink = $externalLinks->get($source->source->value))
                    @php($externalUrl = $externalLink?->urlFor($source->external_id))
                    @if ($externalUrl)
                        <a href="{{ $externalUrl }}" target="_blank" rel="noopener"><i class="feather icon-external-link"></i> {{ __('vessels::vessels.sources.open') }}</a>
                    @elseif ($externalLink?->is_copy_fallback && $source->external_id)
                        <button type="button" class="btn btn-default btn-sm" onclick="navigator.clipboard.writeText(@js($source->external_id))"><i class="feather icon-copy"></i> {{ __('vessels::vessels.sources.copy') }}</button>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <x-ui.empty-row colspan="6" />
        @endforelse
    </x-ui.table>
</x-ui.card>

<x-ui.card :title="__('vessels::vessels.provenance.title')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('vessels::vessels.provenance.field') }}</th>
            <th>{{ __('vessels::vessels.provenance.source') }}</th>
            <th>{{ __('vessels::vessels.provenance.updated_at') }}</th>
        </x-slot:head>
        @forelse ($vessel->fieldProvenance->sortBy('field') as $provenance)
            <tr>
                <td>{{ __('vessels::vessels.provenance.fields.'.$provenance->field) }}</td>
                <td>{{ __($provenance->source->label()) }}</td>
                <td>{{ $provenance->updated_at->isoFormat('L LT') }}</td>
            </tr>
        @empty
            <x-ui.empty-row colspan="3" />
        @endforelse
    </x-ui.table>
</x-ui.card>
