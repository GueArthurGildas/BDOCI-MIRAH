<x-ui.card :title="__('profile::profile.urgencies.title')" :subtitle="__('profile::profile.urgencies.hint')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('profile::profile.columns.kind') }}</th>
            <th>{{ __('profile::profile.columns.date') }}</th>
            <th>{{ __('profile::profile.columns.vessel') }}</th>
            <th>{{ __('profile::profile.columns.detail') }}</th>
        </x-slot:head>
        @foreach ($space['urgentAlerts'] as $alert)
            <tr>
                <td><span class="label label-danger">{{ __('profile::profile.urgencies.alert') }}</span></td>
                <td class="text-nowrap">{{ $alert->triggered_at->isoFormat('L LT') }}</td>
                <td>@if ($alert->vessel)<x-ui.vessel-link :vessel="$alert->vessel" :thumb="false" />@endif</td>
                <td class="text-wrap">
                    @can(App\Support\Authorization\Permission::TrackingView->value)
                        <a href="{{ route('tracking.alerts.show', $alert) }}">{{ __($alert->type->label()) }}</a>
                    @else
                        {{ __($alert->type->label()) }}
                    @endcan
                </td>
            </tr>
        @endforeach
        @foreach ($space['urgentNotes'] as $note)
            <tr>
                <td><span class="label label-warning">{{ __('profile::profile.urgencies.message') }}</span></td>
                <td class="text-nowrap">{{ $note->created_at->isoFormat('L LT') }}</td>
                <td>@if ($note->vessel)<x-ui.vessel-link :vessel="$note->vessel" :thumb="false" />@endif</td>
                <td class="text-wrap">{{ Str::limit($note->free_text, 140) }}</td>
            </tr>
        @endforeach
        @if ($space['urgentAlerts']->isEmpty() && $space['urgentNotes']->isEmpty())
            <x-ui.empty-row colspan="4">{{ __('profile::profile.empty') }}</x-ui.empty-row>
        @endif
    </x-ui.table>
</x-ui.card>
