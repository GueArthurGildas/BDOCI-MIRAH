<x-layouts.app :title="__('vessels::vessels.duplicates.title')">
    <x-ui.page-header :title="__('vessels::vessels.duplicates.title')" :subtitle="__('vessels::vessels.duplicates.subtitle')" :breadcrumbs="[__('vessels::vessels.title') => route('vessels.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('vessels::vessels.duplicates.list_title')" :subtitle="trans_choice('ui.results_count', $sources->total())" flush>
            <x-ui.table :paginator="$sources">
                <x-slot:head>
                    <th>{{ __('vessels::vessels.duplicates.incoming') }}</th>
                    <th>{{ __('vessels::vessels.duplicates.existing') }}</th>
                    <th>{{ __('vessels::vessels.duplicates.criterion') }}</th>
                    <th>{{ __('vessels::vessels.duplicates.decision') }}</th>
                </x-slot:head>

                @forelse ($sources as $source)
                    <tr>
                        <td>
                            <div class="f-w-600">{{ $source->vessel?->name }}</div>
                            <div class="text-muted f-12">{{ __($source->source->label()) }} {{ $source->external_id }}</div>
                            <div class="text-muted f-12">{{ $source->vessel?->flagName() }}</div>
                            @foreach ($source->vessel?->activeIdentifiers ?? [] as $identifier)
                                <div class="f-12">{{ __($identifier->type->label()) }} : {{ $identifier->value }}</div>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('vessels.show', $source->candidate) }}" class="f-w-600">{{ $source->candidate->name }}</a>
                            <div class="text-muted f-12">{{ $source->candidate->flagName() }}</div>
                            @foreach ($source->candidate->activeIdentifiers as $identifier)
                                <div class="f-12">{{ __($identifier->type->label()) }} : {{ $identifier->value }}</div>
                            @endforeach
                        </td>
                        <td>{{ $source->matching_criterion ? __($source->matching_criterion->label()) : '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('vessels.duplicates.settle', $source) }}" class="d-flex flex-wrap gap-1">
                                @csrf
                                <button name="decision" value="merge" class="btn btn-primary btn-sm">{{ __('enums.duplicate_decision.merge') }}</button>
                                <button name="decision" value="keep_separate" class="btn btn-default btn-sm">{{ __('enums.duplicate_decision.keep_separate') }}</button>
                                <button name="decision" value="reject" class="btn btn-outline-danger btn-sm">{{ __('enums.duplicate_decision.reject') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="4">{{ __('vessels::vessels.duplicates.empty') }}</x-ui.empty-row>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
