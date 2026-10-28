<x-layouts.app :title="__('logbook::logbook.days.title')">
    <x-ui.page-header :title="__('logbook::logbook.days.title')" :subtitle="__('logbook::logbook.days.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('logbook::logbook.days.list_title')" :subtitle="trans_choice('ui.results_count', $days->total())" flush>
            <x-slot:actions>
                <a href="{{ route('logbook.days.today') }}" class="btn btn-primary btn-sm">
                    <i class="feather icon-calendar"></i> {{ __('logbook::logbook.days.open_today') }}
                </a>
            </x-slot:actions>

            <x-ui.table :paginator="$days">
                <x-slot:head>
                    <th>{{ __('logbook::logbook.days.fields.day') }}</th>
                    <th>{{ __('logbook::logbook.days.fields.shift') }}</th>
                    <th>{{ __('logbook::logbook.days.fields.entries') }}</th>
                    <th>{{ __('logbook::logbook.days.fields.status') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($days as $day)
                    <tr>
                        <td class="text-nowrap">{{ $day->day->isoFormat('dddd L') }}</td>
                        <td class="text-nowrap">{{ $day->shift_start_at->format('H:i') }} - {{ $day->shift_end_at->format('H:i') }}</td>
                        <td>{{ $day->entries_count }}</td>
                        <td><x-ui.status-badge :status="$day->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('logbook.days.show', $day) }}" class="btn btn-outline-primary btn-mini">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
