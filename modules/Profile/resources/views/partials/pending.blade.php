<x-ui.card :title="__('profile::profile.pending.title')" :subtitle="__('profile::profile.pending.hint')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('profile::profile.columns.kind') }}</th>
            <th>{{ __('profile::profile.columns.date') }}</th>
            <th>{{ __('profile::profile.columns.vessel') }}</th>
            <th>{{ __('profile::profile.columns.detail') }}</th>
        </x-slot:head>
        @foreach ($space['observations'] as $observation)
            <tr>
                <td><span class="label label-info">{{ __('profile::profile.pending.observation') }}</span></td>
                <td class="text-nowrap">{{ $observation->observed_at->isoFormat('L LT') }}</td>
                <td>@if ($observation->vessel)<x-ui.vessel-link :vessel="$observation->vessel" :thumb="false" />@endif</td>
                <td class="text-wrap">{{ Str::limit($observation->description, 140) }}</td>
            </tr>
        @endforeach
        @foreach ($space['dueNotes'] as $note)
            <tr>
                <td><span class="label label-warning">{{ __('profile::profile.pending.note') }}</span></td>
                <td class="text-nowrap">{{ $note->due_date->isoFormat('L') }}</td>
                <td>@if ($note->vessel)<x-ui.vessel-link :vessel="$note->vessel" :thumb="false" />@endif</td>
                <td class="text-wrap">{{ Str::limit($note->free_text, 140) }}</td>
            </tr>
        @endforeach
        @foreach ($space['operations'] as $operation)
            <tr>
                <td><span class="label label-primary">{{ __('profile::profile.pending.operation') }}</span></td>
                <td class="text-nowrap">{{ $operation->started_at?->isoFormat('L') }}</td>
                <td>—</td>
                <td class="text-wrap">
                    @can(App\Support\Authorization\Permission::OperationsView->value)
                        <a href="{{ route('operations.show', $operation) }}">{{ $operation->code }}</a>
                    @else
                        {{ $operation->code }}
                    @endcan
                </td>
            </tr>
        @endforeach
        @if ($space['observations']->isEmpty() && $space['dueNotes']->isEmpty() && $space['operations']->isEmpty())
            <x-ui.empty-row colspan="4">{{ __('profile::profile.empty') }}</x-ui.empty-row>
        @endif
    </x-ui.table>
</x-ui.card>
