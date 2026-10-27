<x-ui.card :title="__('administration::administration.connectors.tabs.errors')" :subtitle="__('administration::administration.connectors.limit_hint', ['count' => $limit])" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('administration::administration.connectors.columns.date') }}</th>
            <th>{{ __('administration::administration.connectors.columns.code') }}</th>
            <th>{{ __('administration::administration.connectors.columns.message') }}</th>
            <th>{{ __('administration::administration.connectors.columns.status') }}</th>
            @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                <th></th>
            @endcan
        </x-slot:head>
        @forelse ($errors as $error)
            <tr>
                <td>{{ $error->occurred_at->isoFormat('L LT') }}</td>
                <td><span class="label label-danger">{{ __($error->code->label()) }}</span></td>
                <td class="f-12">{{ $error->message }}</td>
                <td>
                    @if ($error->isResolved())
                        <span class="label label-success">{{ __('administration::administration.connectors.resolved') }}</span>
                        <div class="text-muted f-12">{{ collect([$error->resolver?->name, $error->resolved_at->diffForHumans()])->filter()->implode(' · ') }}</div>
                    @else
                        <span class="label label-warning">{{ __('administration::administration.connectors.open') }}</span>
                    @endif
                </td>
                @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                    <td class="text-end">
                        <form method="POST" action="{{ route($error->isResolved() ? 'administration.connectors.errors.reopen' : 'administration.connectors.errors.resolve', $error) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $error->isResolved() ? 'btn-default' : 'btn-outline-success' }}">
                                {{ __($error->isResolved() ? 'administration::administration.connectors.reopen' : 'administration::administration.connectors.resolve') }}
                            </button>
                        </form>
                    </td>
                @endcan
            </tr>
        @empty
            <x-ui.empty-row :colspan="5">{{ __('administration::administration.connectors.no_errors') }}</x-ui.empty-row>
        @endforelse
    </x-ui.table>
</x-ui.card>
