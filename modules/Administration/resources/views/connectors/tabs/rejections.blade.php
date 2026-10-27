<x-ui.card :title="__('administration::administration.connectors.tabs.rejections')" :subtitle="__('administration::administration.connectors.limit_hint', ['count' => $limit])" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('administration::administration.connectors.columns.received_at') }}</th>
            <th>{{ __('administration::administration.connectors.columns.reference') }}</th>
            <th>{{ __('administration::administration.connectors.columns.reason') }}</th>
            <th>{{ __('administration::administration.connectors.columns.payload') }}</th>
            <th>{{ __('administration::administration.connectors.columns.status') }}</th>
            @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                <th></th>
            @endcan
        </x-slot:head>
        @forelse ($rejections as $rejection)
            <tr>
                <td>{{ $rejection->received_at->isoFormat('L LT') }}</td>
                <td>{{ $rejection->external_ref ?? '—' }}</td>
                <td>
                    <span class="label label-warning">{{ __($rejection->reason->label()) }}</span>
                    @if ($rejection->details)
                        <div class="text-muted f-12 m-t-5">{{ $rejection->details }}</div>
                    @endif
                </td>
                <td>
                    <details>
                        <summary class="f-12">{{ __('administration::administration.connectors.show_payload') }}</summary>
                        <pre class="f-12 m-t-5 m-b-0">{{ json_encode($rejection->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                </td>
                <td>
                    <x-ui.status-badge :status="$rejection->status" />
                    @if ($rejection->handled_at)
                        <div class="text-muted f-12">{{ collect([$rejection->handler?->name, $rejection->handled_at->diffForHumans()])->filter()->implode(' · ') }}</div>
                    @endif
                </td>
                @can(App\Support\Authorization\Permission::ConnectorsManage->value)
                    <td class="text-end text-nowrap">
                        @foreach (($rejection->status === App\Enums\RejectionStatus::Pending ? [App\Enums\RejectionStatus::Corrected, App\Enums\RejectionStatus::Dismissed] : [App\Enums\RejectionStatus::Pending]) as $target)
                            <form method="POST" action="{{ route('administration.connectors.rejections.handle', $rejection) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $target->value }}">
                                <button type="submit" class="btn btn-sm {{ $target === App\Enums\RejectionStatus::Corrected ? 'btn-outline-success' : 'btn-default' }}">
                                    {{ __('administration::administration.connectors.mark_'.$target->value) }}
                                </button>
                            </form>
                        @endforeach
                    </td>
                @endcan
            </tr>
        @empty
            <x-ui.empty-row :colspan="6">{{ __('administration::administration.connectors.no_rejections') }}</x-ui.empty-row>
        @endforelse
    </x-ui.table>
</x-ui.card>
