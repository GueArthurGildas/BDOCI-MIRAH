<x-layouts.app :title="__('movements::eez.alerts_title')">
    <x-ui.page-header :title="__('movements::eez.alerts_title')" :subtitle="__('movements::eez.alerts_subtitle')" />

    <div class="page-body">
        @include('movements::eez._tabs')

        <x-ui.filters :action="route('movements.eez.alerts')">
            <x-ui.filter-select name="status" :label="__('movements::eez.fields.status')" :options="App\Enums\EntryExitAlertStatus::options()" :value="$filters['status'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('movements::eez.alerts_title')" :subtitle="trans_choice('ui.results_count', $alerts->total())" flush>
            <x-ui.table :paginator="$alerts">
                <x-slot:head>
                    <th>{{ __('movements::eez.fields.alerted_at') }}</th>
                    <th>{{ __('movements::eez.fields.direction') }}</th>
                    <th>{{ __('movements::eez.fields.vessel') }}</th>
                    <th>{{ __('movements::eez.fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($alerts as $alert)
                    <tr>
                        <td class="text-nowrap">{{ $alert->alerted_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$alert->direction" /></td>
                        <td><x-ui.vessel-link :vessel="$alert->vessel" /></td>
                        <td><x-ui.status-badge :status="$alert->status" /></td>
                        <td class="text-end text-nowrap">
                            @if ($alert->declaration)
                                <a href="{{ route('movements.eez.show', $alert->declaration) }}" class="btn btn-default btn-sm"><i class="feather icon-eye"></i></a>
                            @endif
                            @can(App\Support\Authorization\Permission::MovementsDeclare->value)
                                @if ($alert->status === App\Enums\EntryExitAlertStatus::Open)
                                    <form method="POST" action="{{ route('movements.eez.alerts.close', $alert) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-sm">{{ __('movements::eez.close_alert') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
