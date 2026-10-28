<x-layouts.app :title="__('movements::eez.missed_title')">
    <x-ui.page-header :title="__('movements::eez.missed_title')" :subtitle="__('movements::eez.missed_subtitle')" />

    <div class="page-body">
        @include('movements::eez._tabs')

        <x-ui.filters :action="route('movements.eez.missed.index')">
            <x-ui.filter-select name="resolved" :label="__('movements::eez.resolved_filter')" :options="['0' => __('movements::eez.unresolved'), '1' => __('movements::eez.resolved')]" :value="$filters['resolved'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('movements::eez.missed_title')" :subtitle="trans_choice('ui.results_count', $missed->total())" flush>
            <x-ui.table :paginator="$missed">
                <x-slot:head>
                    <th>{{ __('movements::eez.fields.detected_at') }}</th>
                    <th>{{ __('movements::eez.fields.vessel') }}</th>
                    <th>{{ __('movements::eez.fields.reminders') }}</th>
                    <th>{{ __('movements::eez.fields.qualification') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($missed as $row)
                    <tr>
                        <td class="text-nowrap">{{ $row->detected_at->isoFormat('L LT') }}</td>
                        <td><x-ui.vessel-link :vessel="$row->vessel" /></td>
                        <td>{{ $row->reminders }}</td>
                        <td>{{ $row->qualification ?? '—' }}</td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::MovementsDeclare->value)
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('movements.eez.missed.qualify', $row) }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="qualification" class="form-control form-control-sm" maxlength="120" value="{{ $row->qualification }}" aria-label="{{ __('movements::eez.fields.qualification') }}" required>
                                        <button type="submit" class="btn btn-default btn-sm">{{ __('movements::eez.qualify') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('movements.eez.missed.remind', $row) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-sm">{{ __('movements::eez.remind') }}</button>
                                    </form>
                                </div>
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
