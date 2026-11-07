<x-ui.card :title="__('profile::profile.alerts.title')" :subtitle="__('profile::profile.alerts.hint')" flush>
    <x-ui.table>
        <x-slot:head>
            <th>{{ __('profile::profile.columns.category') }}</th>
            <th>{{ __('profile::profile.columns.date') }}</th>
            <th>{{ __('profile::profile.columns.vessel') }}</th>
            <th>{{ __('profile::profile.columns.detail') }}</th>
            <th>{{ __('profile::profile.columns.status') }}</th>
        </x-slot:head>
        @php($shown = 0)
        @foreach (App\Enums\AlertCategory::cases() as $category)
            @foreach ($space['alerts'][$category->value] as $alert)
                @php($shown++)
                <tr>
                    <td><x-ui.status-badge :status="$category" /></td>
                    <td class="text-nowrap">{{ $alert->triggered_at->isoFormat('L LT') }}</td>
                    <td>@if ($alert->vessel)<x-ui.vessel-link :vessel="$alert->vessel" :thumb="false" />@endif</td>
                    <td class="text-wrap">
                        @can(App\Support\Authorization\Permission::TrackingView->value)
                            <a href="{{ route('tracking.alerts.show', $alert) }}">{{ __($alert->type->label()) }}</a>
                        @else
                            {{ __($alert->type->label()) }}
                        @endcan
                    </td>
                    <td><x-ui.status-badge :status="$alert->status" /></td>
                </tr>
            @endforeach
        @endforeach
        @if ($shown === 0)
            <x-ui.empty-row colspan="5">{{ __('profile::profile.empty') }}</x-ui.empty-row>
        @endif
    </x-ui.table>
    @can(App\Support\Authorization\Permission::TrackingView->value)
        <div class="card-footer text-end"><a href="{{ route('tracking.alerts.index') }}">{{ __('tracking::tracking.alerts.see_all') }}</a></div>
    @endcan
</x-ui.card>
