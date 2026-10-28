<x-layouts.app :title="__('reports::reports.archives.title')">
    <x-ui.page-header :title="__('reports::reports.archives.title')" :subtitle="__('reports::reports.archives.subtitle', ['years' => $years])" />

    <div class="page-body">
        <x-ui.card :title="__('reports::reports.archives.pending_title')">
            <p>{{ trans_choice('reports::reports.archives.pending', $eligibleCount, ['count' => $eligibleCount, 'date' => $cutoff->isoFormat('L')]) }}</p>
            @can(App\Support\Authorization\Permission::ReportsArchive->value)
                <form method="POST" action="{{ route('reports.archives.run') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" @disabled($eligibleCount === 0)>
                        <i class="feather icon-archive"></i> {{ __('reports::reports.archives.run') }}
                    </button>
                </form>
            @endcan
        </x-ui.card>

        <x-ui.card :title="__('reports::reports.archives.list_title')" :subtitle="trans_choice('ui.results_count', $archives->total())" flush>
            <x-ui.table :paginator="$archives">
                <x-slot:head>
                    <th>{{ __('reports::reports.columns.code') }}</th>
                    <th>{{ __('reports::reports.columns.name') }}</th>
                    <th>{{ __('reports::reports.columns.category') }}</th>
                    <th>{{ __('reports::reports.columns.ended_at') }}</th>
                    <th>{{ __('reports::reports.archives.archived_at') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($archives as $archive)
                    <tr>
                        <td class="text-nowrap">{{ $archive->operation->code }}</td>
                        <td>{{ $archive->operation->name }}</td>
                        <td>{{ $archive->operation->category ? __($archive->operation->category->code->label()) : '' }}</td>
                        <td class="text-nowrap">{{ $archive->operation->ended_at?->isoFormat('L') }}</td>
                        <td class="text-nowrap">{{ $archive->archived_at->isoFormat('L LT') }}</td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::OperationsView->value)
                                <a href="{{ route('operations.show', $archive->operation) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.view') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
