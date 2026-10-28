<x-layouts.app :title="__('missions::missions.title')">
    <x-ui.page-header :title="__('missions::missions.title')" :subtitle="__('missions::missions.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('missions.index')">
            <x-ui.filter-select name="status" :label="__('missions::missions.fields.status')" :options="App\Enums\MissionStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="type" :label="__('missions::missions.fields.type')" :options="App\Enums\MissionType::options()" :value="$filters['type'] ?? null" />
            <x-ui.filter-select name="nature" :label="__('missions::missions.fields.nature')" :options="App\Enums\MissionNature::options()" :value="$filters['nature'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('missions::missions.list_title')" :subtitle="trans_choice('ui.results_count', $missions->total())" flush>
            <x-slot:actions>
                @can(App\Support\Authorization\Permission::MissionsCreate->value)
                    <a href="{{ route('missions.create') }}" class="btn btn-primary btn-sm"><i class="feather icon-plus"></i> {{ __('missions::missions.schedule') }}</a>
                @endcan
            </x-slot:actions>
            <x-ui.table :paginator="$missions">
                <x-slot:head>
                    <th>{{ __('missions::missions.fields.code') }}</th>
                    <th>{{ __('missions::missions.fields.planned_start_at') }}</th>
                    <th>{{ __('missions::missions.fields.type') }}</th>
                    <th>{{ __('missions::missions.fields.nature') }}</th>
                    <th>{{ __('missions::missions.fields.objectives') }}</th>
                    <th>{{ __('missions::missions.fields.zones') }}</th>
                    <th>{{ __('missions::missions.fields.status') }}</th>
                </x-slot:head>
                @forelse ($missions as $mission)
                    <tr>
                        <td><a href="{{ route('missions.show', $mission) }}" class="f-w-600">{{ $mission->code }}</a></td>
                        <td class="text-nowrap">{{ $mission->startsAt()?->isoFormat('L') ?? '—' }}</td>
                        <td>{{ __($mission->type->label()) }}</td>
                        <td>{{ __($mission->nature->label()) }}</td>
                        <td class="text-wrap">{{ $mission->objectives }}</td>
                        <td>{{ collect($mission->zones)->pluck('name')->filter()->join(', ') ?: '—' }}</td>
                        <td><x-ui.status-badge :status="$mission->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        <x-ui.card :title="__('missions::missions.never_inspected')" :subtitle="__('missions::missions.never_inspected_hint')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('missions::missions.fields.vessel_id') }}</th>
                    <th>{{ __('missions::missions.fields.sighted_count') }}</th>
                </x-slot:head>
                @forelse ($neverInspected as $row)
                    <tr>
                        <td>
                            <a href="{{ route('vessels.show', $row->id) }}">{{ $row->name }}</a>
                            <div class="text-muted f-12">{{ $row->registration_number }}</div>
                        </td>
                        <td>{{ $row->sighted_count }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="2" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
