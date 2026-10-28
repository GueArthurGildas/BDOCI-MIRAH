<x-layouts.app :title="__('reports::reports.exports.title')">
    <x-ui.page-header :title="__('reports::reports.exports.title')" :subtitle="__('reports::reports.exports.subtitle')" />

    <div class="page-body">
        @forelse ($types as $type)
            <x-ui.card :title="__($type->label())">
                <form method="GET" action="{{ route('reports.exports.download', $type->value) }}" class="row g-3 align-items-end">
                    @if ($type->value === 'operations')
                        <x-ui.filter-input name="year" type="number" :label="__('reports::reports.exports.fields.year')" />
                        <x-ui.filter-select name="status" :label="__('reports::reports.exports.fields.status')" :options="$operationStatuses" />
                    @elseif ($type->value === 'vessels')
                        <x-ui.filter-input name="search" type="search" :label="__('reports::reports.exports.fields.search')" />
                        <x-ui.filter-select name="record_status" :label="__('reports::reports.exports.fields.record_status')" :options="$recordStatuses" />
                    @else
                        <x-ui.filter-input name="from" type="date" :label="__('reports::reports.exports.fields.from')" />
                        <x-ui.filter-input name="to" type="date" :label="__('reports::reports.exports.fields.to')" />
                    @endif
                    <div class="col-md-2">
                        <label class="form-label" for="format-{{ $type->value }}">{{ __('reports::reports.exports.fields.format') }}</label>
                        <select id="format-{{ $type->value }}" name="format" class="form-select">
                            @foreach ($formats as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-download"></i> {{ __('reports::reports.exports.download') }}</button>
                    </div>
                </form>
            </x-ui.card>
        @empty
            <x-ui.card :title="__('reports::reports.exports.title')">
                <p class="text-muted m-0">{{ __('reports::reports.exports.none_allowed') }}</p>
            </x-ui.card>
        @endforelse

        <x-ui.card :title="__('reports::reports.exports.log_title')" :subtitle="trans_choice('ui.results_count', $log->total())" flush>
            <x-ui.table :paginator="$log">
                <x-slot:head>
                    <th>{{ __('reports::reports.exports.columns.exported_at') }}</th>
                    <th>{{ __('reports::reports.exports.columns.user') }}</th>
                    <th>{{ __('reports::reports.exports.columns.subject') }}</th>
                    <th>{{ __('reports::reports.exports.columns.format') }}</th>
                    <th>{{ __('reports::reports.exports.columns.filters') }}</th>
                    <th>{{ __('reports::reports.exports.columns.row_count') }}</th>
                </x-slot:head>

                @forelse ($log as $entry)
                    <tr>
                        <td class="text-nowrap">{{ $entry->exported_at->isoFormat('L LT') }}</td>
                        <td>{{ $entry->user->name }}</td>
                        <td>{{ __('reports::reports.exports.types.'.$entry->subject) }}</td>
                        <td>{{ __($entry->format->label()) }}</td>
                        <td>
                            @forelse ($entry->filters ?? [] as $key => $value)
                                <div>{{ __('reports::reports.exports.fields.'.$key) }} : {{ $value }}</div>
                            @empty
                                <span class="text-muted">{{ __('reports::reports.exports.no_filter') }}</span>
                            @endforelse
                        </td>
                        <td>{{ $entry->row_count }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
