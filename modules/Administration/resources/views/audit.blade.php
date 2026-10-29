<x-layouts.app :title="__('administration::administration.audit.title')">
    <x-ui.page-header :title="__('administration::administration.audit.title')" :subtitle="__('administration::administration.audit.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('administration::administration.audit.list_title')" :subtitle="trans_choice('ui.results_count', $activities->total())" flush>
            <x-ui.table-toolbar :action="route('administration.audit')" :paginator="$activities">
                <x-ui.toolbar-select name="user" :label="__('administration::administration.audit.user')" :options="$users" :value="$filters['user'] ?? null" />
                <x-ui.toolbar-select name="event" :label="__('administration::administration.audit.event')" :options="$events" :value="$filters['event'] ?? null" />
                <x-ui.toolbar-input name="from" type="date" :label="__('administration::administration.audit.from')" :value="$filters['from'] ?? null" />
                <x-ui.toolbar-input name="to" type="date" :label="__('administration::administration.audit.to')" :value="$filters['to'] ?? null" />
            </x-ui.table-toolbar>

            <x-ui.table :paginator="$activities">
                <x-slot:head>
                    <th>{{ __('administration::administration.audit.date') }}</th>
                    <th>{{ __('administration::administration.audit.user') }}</th>
                    <th>{{ __('administration::administration.audit.event') }}</th>
                    <th>{{ __('administration::administration.audit.subject') }}</th>
                    <th>{{ __('administration::administration.audit.details') }}</th>
                </x-slot:head>

                <x-slot:filters>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </x-slot:filters>
                @forelse ($activities as $activity)
                    <tr>
                        <td class="text-nowrap">{{ $activity->created_at->isoFormat('L LTS') }}</td>
                        <td>{{ $activity->causer?->name ?? __('administration::administration.audit.system') }}</td>
                        <td>{{ $events->get($activity->event, $activity->event) }}</td>
                        <td>
                            @if ($activity->subject_type)
                                {{ __('administration::administration.audit.subjects.'.Str::snake(class_basename($activity->subject_type))) }} #{{ $activity->subject_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-wrap text-muted f-12">
                            @if ($changedFields = array_keys($activity->attribute_changes['attributes'] ?? []))
                                {{ __('administration::administration.audit.changed_fields', ['fields' => implode(', ', $changedFields)]) }}
                            @endif
                            {{ collect($activity->properties)->only(['email', 'ip'])->implode(' · ') }}
                            @if ($added = $activity->properties->get('added'))
                                <div class="text-success">+ {{ implode(', ', $added) }}</div>
                            @endif
                            @if ($removed = $activity->properties->get('removed'))
                                <div class="text-danger">− {{ implode(', ', $removed) }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
