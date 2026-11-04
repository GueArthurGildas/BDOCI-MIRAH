<x-layouts.app :title="__('inspections::never.title')">
    <x-ui.page-header :title="__('inspections::never.title')" :subtitle="__('inspections::never.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('inspections::never.list_title')" :subtitle="trans_choice('ui.results_count', $vessels->total())" flush>
            <x-ui.table-toolbar :action="route('inspections.never-inspected')" :paginator="$vessels">
                <x-ui.toolbar-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('inspections::never.search_placeholder')" />
                <label class="gs-toolbar-field">
                    {{ __('inspections::never.sightings') }}
                    <select name="min" class="form-select form-select-sm" data-auto-submit data-native>
                        @foreach ($thresholds as $value => $label)
                            <option value="{{ $value }}" @selected($value === $minimum)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <x-ui.toolbar-input name="from" type="date" :label="__('inspections::never.seen_from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input name="to" type="date" :label="__('inspections::never.seen_to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$vessels">
                <x-slot:head>
                    <th>{{ __('inspections::never.vessel') }}</th>
                    <th>{{ __('inspections::never.flag') }}</th>
                    <th class="text-end">{{ __('inspections::never.sighted_count') }}</th>
                    <th>{{ __('inspections::never.last_seen') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($vessels as $vessel)
                    <tr>
                        <td><x-ui.vessel-link :vessel="$vessel" /></td>
                        <td>{{ $vessel->flagName() ?: '—' }}</td>
                        <td class="text-end f-w-600">{{ $vessel->sighted_count }}</td>
                        <td class="text-nowrap">{{ $vessel->last_seen_at ? \Illuminate\Support\Carbon::parse($vessel->last_seen_at)->isoFormat('L LT') : '—' }}</td>
                        <td class="text-end">
                            @can(App\Support\Authorization\Permission::MissionsCreate->value)
                                <a href="{{ route('missions.create') }}" class="btn btn-outline-primary btn-mini">{{ __('inspections::never.plan_mission') }}</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('inspections::never.how_title')">
            <p class="mb-0">{{ __('inspections::never.how_text') }}</p>
        </x-ui.card>
    </div>
</x-layouts.app>
