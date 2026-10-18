<x-layouts.app :title="__('missions::missions.title')">
    <x-ui.page-header :title="__('missions::missions.title')" :subtitle="__('missions::missions.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('missions.index')">
            <x-ui.filter-select name="status" :label="__('missions::missions.fields.status')" :options="App\Enums\MissionStatus::options()" :value="$filters['status'] ?? null" />
            <x-ui.filter-select name="type" :label="__('missions::missions.fields.type')" :options="App\Enums\MissionType::options()" :value="$filters['type'] ?? null" />
        </x-ui.filters>

        <x-ui.card :title="__('missions::missions.list_title')" :subtitle="trans_choice('ui.results_count', $missions->total())" flush>
            <x-ui.table :paginator="$missions">
                <x-slot:head>
                    <th>{{ __('missions::missions.fields.reference') }}</th>
                    <th>{{ __('missions::missions.fields.starts_at') }}</th>
                    <th>{{ __('missions::missions.fields.type') }}</th>
                    <th>{{ __('missions::missions.fields.objective') }}</th>
                    <th>{{ __('missions::missions.fields.zone') }}</th>
                    <th>{{ __('missions::missions.fields.status') }}</th>
                </x-slot:head>
                @forelse ($missions as $mission)
                    <tr>
                        <td><a href="{{ route('missions.show', $mission) }}" class="f-w-600">{{ $mission->reference }}</a></td>
                        <td class="text-nowrap">{{ $mission->starts_at->isoFormat('L') }}</td>
                        <td>{{ __($mission->type->label()) }}</td>
                        <td class="text-wrap">{{ $mission->objective }}</td>
                        <td>{{ $mission->zone?->name ?? '—' }}</td>
                        <td><x-ui.status-badge :status="$mission->status" /></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
