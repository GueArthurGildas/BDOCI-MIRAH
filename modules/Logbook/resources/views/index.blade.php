<x-layouts.app :title="__('logbook::logbook.title')">
    <x-ui.page-header :title="__('logbook::logbook.title')" :subtitle="__('logbook::logbook.subtitle')" />

    <div class="page-body">
        <x-ui.filters :action="route('logbook.index')">
            <x-ui.filter-field :label="__('ui.search')" class="col-md-3">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('logbook::logbook.search_placeholder') }}">
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('logbook::logbook.fields.status')">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach (App\Enums\ObservationStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('logbook::logbook.fields.level')">
                <select name="level" class="form-select form-select-sm">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach (App\Enums\ObservationLevel::options() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['level'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('logbook::logbook.fields.team')">
                <select name="team" class="form-select form-select-sm">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach ($teams as $id => $name)
                        <option value="{{ $id }}" @selected((string) ($filters['team'] ?? '') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </x-ui.filter-field>
            <x-ui.filter-field :label="__('logbook::logbook.pending_only')" class="col-md-auto">
                <div class="form-check form-switch m-t-5">
                    <input type="checkbox" name="pending" value="1" class="form-check-input" @checked($filters['pending'] ?? false) title="{{ __('logbook::logbook.pending_hint') }}">
                </div>
            </x-ui.filter-field>
        </x-ui.filters>

        <x-ui.card :title="__('logbook::logbook.list_title')" :subtitle="trans_choice('ui.results_count', $observations->total())" flush>
            @can(App\Support\Authorization\Permission::LogbookCreate->value)
                <x-slot:actions>
                    <a href="{{ route('logbook.create') }}" class="btn btn-primary btn-sm">
                        <i class="feather icon-plus"></i> {{ __('logbook::logbook.new') }}
                    </a>
                </x-slot:actions>
            @endcan

            <x-ui.table :paginator="$observations">
                <x-slot:head>
                    <th>{{ __('logbook::logbook.fields.observed_at') }}</th>
                    <th>{{ __('logbook::logbook.fields.level') }}</th>
                    <th>{{ __('logbook::logbook.fields.vessel') }}</th>
                    <th>{{ __('logbook::logbook.fields.description') }}</th>
                    <th>{{ __('logbook::logbook.fields.team') }}</th>
                    <th>{{ __('logbook::logbook.fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($observations as $observation)
                    <tr>
                        <td class="text-nowrap">{{ $observation->observed_at->isoFormat('L LT') }}</td>
                        <td><x-ui.status-badge :status="$observation->level" /></td>
                        <td>
                            @if ($observation->vessel)
                                <x-ui.vessel-link :vessel="$observation->vessel" />
                            @else
                                <span class="text-muted">{{ __('logbook::logbook.no_vessel') }}</span>
                            @endif
                        </td>
                        <td class="text-wrap">{{ Str::limit($observation->description, 90) }}</td>
                        <td>
                            {{ $observation->team?->name }}
                            <div class="text-muted f-12">{{ $observation->author->name }}</div>
                        </td>
                        <td><x-ui.status-badge :status="$observation->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('logbook.show', $observation) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
