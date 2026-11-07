@php
    $link = fn (int $caseId, string $folder = 'summary') => route('infractions.index', [...$keep, 'view' => 'folders', 'case' => $caseId, 'folder' => $folder]);
    $icons = ['upheld' => 'icon-alert-triangle', 'evidence' => 'icon-paperclip', 'seals' => 'icon-lock', 'measures' => 'icon-shield', 'seizures' => 'icon-package', 'follow_ups' => 'icon-git-merge', 'actors' => 'icon-users'];
@endphp

<x-ui.filters :action="route('infractions.index')">
    <input type="hidden" name="view" value="folders">
    <x-ui.filter-input name="search" type="search" :label="__('ui.search')" :value="$filters['search'] ?? null" :placeholder="__('infractions::infractions.search_placeholder')" data-auto-submit class="col-md-4" />
    <x-ui.filter-select name="status" :label="__('infractions::infractions.fields.status')" :options="App\Enums\InfractionCaseStatus::options()" :value="$filters['status'] ?? null" data-auto-submit class="col-md-2" />
    <x-ui.filter-select name="type" :label="__('infractions::infractions.fields.type')" :options="$types" :value="$filters['type'] ?? null" data-auto-submit class="col-md-3" />
    <x-ui.filter-input name="from" type="date" :label="__('infractions::infractions.fields.from')" :value="$filters['from'] ?? null" data-auto-submit class="col-md-1" />
    <x-ui.filter-input name="to" type="date" :label="__('infractions::infractions.fields.to')" :value="$filters['to'] ?? null" data-auto-submit class="col-md-1" />
</x-ui.filters>

@if ($cases->isEmpty())
    <x-ui.card :title="__('infractions::infractions.list_title')"><p class="text-muted mb-0">{{ __('ui.no_results') }}</p></x-ui.card>
@else
    <div class="row">
        <div class="col-xl-3">
            <x-ui.card class="gs-tree-card" :title="__('infractions::infractions.folders.title')" :subtitle="trans_choice('ui.results_count', $cases->count())">
                <x-slot:actions>
                    <ul class="list-unstyled card-option">
                        <li><a href="#" data-gs-tree="open"><i class="feather icon-plus-square"></i></a></li>
                        <li><a href="#" data-gs-tree="close"><i class="feather icon-minus-square"></i></a></li>
                    </ul>
                </x-slot:actions>

                <ul class="gs-tree" data-gs-tree-root>
                    @foreach ($tree as $status => $statusCases)
                        @continue ($statusCases->isEmpty())
                        <li>
                            <details @if ($selected && $selected->status->value === $status) open @endif>
                                <summary><i class="feather icon-folder"></i> <span>{{ __('enums.infraction_case_status.'.$status) }}</span> <span class="gs-tree-count">{{ $statusCases->count() }}</span></summary>
                                <ul>
                                    @foreach ($statusCases as $case)
                                        @php($isSelected = $selected && $selected->id === $case->id)
                                        <li>
                                            @unless ($isSelected)
                                                <a href="{{ $link($case->id) }}" class="gs-tree-leaf"><i class="feather icon-file-text"></i> <span>{{ $case->reference }}</span></a>
                                            @else
                                                <details open>
                                                    <summary class="is-active"><a href="{{ $link($case->id) }}"><i class="feather icon-file-text"></i> {{ $case->reference }}</a></summary>
                                                    <ul>
                                                        @foreach (['upheld', 'evidence', 'seals', 'measures', 'seizures', 'follow_ups', 'actors'] as $key)
                                                            <li>
                                                                <a href="{{ $link($case->id, $key) }}" @class(['gs-tree-leaf', 'is-active' => $folder === $key])>
                                                                    <i class="feather {{ $icons[$key] }}"></i> <span>{{ __('infractions::infractions.folders.'.$key) }}</span>
                                                                    <span class="gs-tree-count">{{ $content[$key]->count() }}</span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </details>
                                            @endunless
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>

        <div class="col-xl-9">
            <x-ui.card :title="$selected->reference" :subtitle="$selected->vessel->name">
                <x-slot:actions>
                    <a href="{{ route('infractions.show', $selected) }}" class="btn btn-primary btn-sm">{{ __('infractions::infractions.folders.open_case') }}</a>
                </x-slot:actions>
                <div class="gs-tiles">
                    <div><small>{{ __('infractions::infractions.fields.status') }}</small><strong><x-ui.status-badge :status="$selected->status" /></strong></div>
                    <div><small>{{ __('infractions::infractions.fields.qualification') }}</small><strong><x-ui.status-badge :status="$selected->qualification" /></strong></div>
                    <div><small>{{ __('infractions::infractions.fields.opened_at') }}</small><strong>{{ $selected->opened_at->isoFormat('L') }}</strong></div>
                    <div><small>{{ __('infractions::infractions.fields.vessel') }}</small><strong><x-ui.vessel-link :vessel="$selected->vessel" :thumb="false" /></strong></div>
                </div>
                @if ($selected->facts)
                    <h6>{{ __('infractions::infractions.folders.facts') }}</h6>
                    <p class="text-pre-line">{{ $selected->facts }}</p>
                @endif
                @if ($folder === 'summary')
                    <div class="row">
                        @foreach (['upheld', 'evidence', 'measures', 'follow_ups'] as $key)
                            <div class="col-md-3">
                                <a href="{{ $link($selected->id, $key) }}" class="gs-widget-link">
                                    <x-ui.stat-card :label="__('infractions::infractions.folders.'.$key)" :value="Number::format($content[$key]->count())" :icon="$icons[$key]" color="warning" compact />
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            @if ($folder !== 'summary')
                <x-ui.card :title="__('infractions::infractions.folders.'.$folder)" :subtitle="trans_choice('infractions::infractions.folders.items_count', $content[$folder]->count(), ['count' => $content[$folder]->count()])" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('infractions::infractions.folders.item') }}</th>
                            <th>{{ __('infractions::infractions.folders.detail') }}</th>
                            <th>{{ __('infractions::infractions.folders.date') }}</th>
                        </x-slot:head>
                        @forelse ($content[$folder] as $row)
                            <tr>
                                <td class="text-wrap">{{ $row['label'] }}</td>
                                <td class="text-wrap">{{ $row['detail'] ?? '—' }}</td>
                                <td class="text-nowrap">{{ $row['date']?->isoFormat('L') ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            @endif
        </div>
    </div>
@endif
