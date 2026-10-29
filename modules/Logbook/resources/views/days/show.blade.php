@php
    $isOpen = $day->status === App\Enums\LogbookDayStatus::Open;
@endphp
<x-layouts.app :title="__('logbook::logbook.days.title')">
    <x-ui.page-header :title="$day->day->isoFormat('dddd LL')" :subtitle="__('logbook::logbook.days.shift_range', ['from' => $day->shift_start_at->format('H:i'), 'to' => $day->shift_end_at->format('H:i')])" :breadcrumbs="[__('logbook::logbook.days.title') => route('logbook.days.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('logbook::logbook.entries.title')" :subtitle="__('logbook::logbook.days.fields.status').' : '.__($day->status->label())" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('logbook::logbook.entries.fields.occurred_at') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.channel') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.content') }}</th>
                            <th>{{ __('logbook::logbook.entries.fields.handling_status') }}</th>
                            <th></th>
                        </x-slot:head>

                        @forelse ($day->entries as $entry)
                            <tr>
                                <td class="text-nowrap">{{ $entry->occurred_at->format('H:i') }}</td>
                                <td>
                                    {{ $entry->channel->label }}
                                    <div class="text-muted f-12">{{ __($entry->direction->label()) }}</div>
                                </td>
                                <td class="text-wrap">
                                    <span class="text-pre-line">{{ $entry->content }}</span>
                                    @if ($entry->is_order_or_decision)
                                        <span class="label label-danger">{{ __('logbook::logbook.entries.order_or_decision') }}</span>
                                    @endif
                                    @if ($entry->corrects)
                                        <div class="text-muted f-12">{{ __('logbook::logbook.entries.corrects', ['time' => $entry->corrects->occurred_at->format('d/m H:i')]) }}</div>
                                    @endif
                                    @foreach ($entry->corrections as $correction)
                                        <div class="text-muted f-12">{{ __('logbook::logbook.entries.corrected_at', ['time' => $correction->occurred_at->format('d/m H:i')]) }}</div>
                                    @endforeach
                                    @foreach ($entry->vessels as $vessel)
                                        <div><x-ui.vessel-link :vessel="$vessel" /></div>
                                    @endforeach
                                    @if ($entry->operation)
                                        <div><a href="{{ route('operations.show', $entry->operation) }}">{{ $entry->operation->code }}</a></div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if ($isOpen)
                                        @can(App\Support\Authorization\Permission::LogbookManage->value)
                                            <form method="POST" action="{{ route('logbook.entries.update', $entry) }}" class="d-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <select name="handling_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    @foreach (App\Enums\LogbookHandlingStatus::cases() as $status)
                                                        <option value="{{ $status->value }}" @selected($entry->handling_status === $status)>{{ __($status->label()) }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            <x-ui.status-badge :status="$entry->handling_status" />
                                        @endcan
                                    @else
                                        <x-ui.status-badge :status="$entry->handling_status" />
                                    @endif
                                </td>
                                <td class="text-end">
                                    @can(App\Support\Authorization\Permission::LogbookCreate->value)
                                        @unless ($entry->corrects_entry_id)
                                            <a href="{{ route('logbook.entries.rectify', $entry) }}" class="btn btn-outline-primary btn-mini">{{ __('logbook::logbook.entries.rectify') }}</a>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @if ($isOpen)
                    @can(App\Support\Authorization\Permission::LogbookCreate->value)
                        <x-ui.card :title="__('logbook::logbook.entries.new')">
                            <form method="POST" action="{{ route('logbook.entries.store') }}">
                                @csrf
                                <x-ui.form.input name="occurred_at" type="datetime-local" :label="__('logbook::logbook.entries.fields.occurred_at')" :value="now()->format('Y-m-d\TH:i')" />
                                <x-ui.form.select name="channel_id" :label="__('logbook::logbook.entries.fields.channel')" :options="$channels" required />
                                <x-ui.form.select name="direction" :label="__('logbook::logbook.entries.fields.direction')" :options="App\Enums\LogbookDirection::options()" value="received" required />
                                <x-ui.form.textarea name="content" :label="__('logbook::logbook.entries.fields.content')" rows="3" required />
                                <x-ui.form.select name="operation_id" :label="__('logbook::logbook.entries.fields.operation')" :options="$operations" />
                                <div class="mb-3 row">
                                    <label for="vessel_ids" class="form-label col-sm-3 col-form-label">{{ __('logbook::logbook.entries.fields.vessels') }}</label>
                                    <div class="col-sm-9">
                                        <select id="vessel_ids" name="vessel_ids[]" multiple size="5" class="form-select">
                                            @foreach ($vessels as $id => $label)
                                                <option value="{{ $id }}" @selected(in_array($id, old('vessel_ids', [])))>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <x-ui.form.checkbox name="is_order_or_decision" :label="__('logbook::logbook.entries.order_or_decision')" />
                                <x-ui.form.actions :cancel="route('logbook.days.show', $day)" />
                            </form>
                        </x-ui.card>
                    @endcan

                    @can(App\Support\Authorization\Permission::LogbookClose->value)
                        <x-ui.card :title="__('logbook::logbook.days.close_title')" :subtitle="__('logbook::logbook.days.close_hint')">
                            <form data-confirm="{{ __('logbook::confirm.close_day') }}" data-confirm-label="{{ __('logbook::confirm.close_day_yes') }}" method="POST" action="{{ route('logbook.days.close', $day) }}">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="feather icon-lock"></i> {{ __('logbook::logbook.days.close') }}</button>
                            </form>
                        </x-ui.card>
                    @endcan
                @else
                    <x-ui.card :title="__('logbook::logbook.days.pdf_title')">
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('logbook::logbook.days.fields.locked_at')">{{ $day->locked_at?->isoFormat('L LT') }}</x-ui.detail>
                            <x-ui.detail :label="__('logbook::logbook.days.fields.closed_by')">{{ $day->closing?->closer?->name }}</x-ui.detail>
                            <x-ui.detail :label="__('logbook::logbook.days.fields.fingerprint')"><span class="f-12 text-break">{{ $day->pdfDocument?->sha256_fingerprint }}</span></x-ui.detail>
                            <x-ui.detail :label="__('logbook::logbook.days.fields.integrity')">
                                @if ($pdfIntact !== null)
                                    <span @class(['label', 'label-success' => $pdfIntact, 'label-danger' => ! $pdfIntact])>{{ $pdfIntact ? __('logbook::logbook.days.intact') : __('logbook::logbook.days.altered') }}</span>
                                @endif
                            </x-ui.detail>
                        </x-ui.detail-list>
                        @if ($day->pdfDocument)
                            <a href="{{ route('logbook.days.pdf', $day) }}" class="btn btn-primary m-t-15"><i class="feather icon-download"></i> {{ __('logbook::logbook.days.download') }}</a>
                        @endif
                    </x-ui.card>

                    @if ($day->closing)
                        <x-ui.card :title="__('logbook::logbook.days.counters_title')">
                            <x-ui.detail-list>
                                <x-ui.detail :label="__('logbook::logbook.days.counters.entries_total')">{{ $day->closing->counters['entries_total'] }}</x-ui.detail>
                                <x-ui.detail :label="__('logbook::logbook.days.counters.received')">{{ $day->closing->counters['received'] }}</x-ui.detail>
                                <x-ui.detail :label="__('logbook::logbook.days.counters.sent')">{{ $day->closing->counters['sent'] }}</x-ui.detail>
                                <x-ui.detail :label="__('logbook::logbook.days.counters.orders_or_decisions')">{{ $day->closing->counters['orders_or_decisions'] }}</x-ui.detail>
                                <x-ui.detail :label="__('logbook::logbook.days.counters.corrections')">{{ $day->closing->counters['corrections'] }}</x-ui.detail>
                            </x-ui.detail-list>
                        </x-ui.card>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
