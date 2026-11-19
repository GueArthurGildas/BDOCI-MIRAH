@php
    $number = fn (string $code) => explode('-', $code)[1] ?? $code;
    $hours = collect(app(App\Support\Logbook\LogbookCalendar::class)->handoverHours())->map(fn (int $hour) => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00')->join(' / ');
@endphp
<x-layouts.app :title="__('logbook::logbook.handovers.create_title')">
    <x-ui.page-header :title="__('logbook::logbook.handovers.create_title')" :subtitle="__('logbook::logbook.handovers.hint', ['hours' => $hours])" :breadcrumbs="[__('logbook::logbook.days.title') => route('logbook.days.index'), $day->day->isoFormat('LL') => route('logbook.days.show', $day)]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="__('logbook::logbook.handovers.title')">
                    <form method="POST" action="{{ route('logbook.handovers.store') }}">
                        @csrf
                        <x-ui.form.input name="handed_over_at" type="datetime-local" :label="__('logbook::logbook.handovers.fields.handed_over_at')" :value="now()->format('Y-m-d\TH:i')" required />
                        <x-ui.form.select name="supervised_by" :label="__('logbook::logbook.handovers.fields.supervisor')" :options="$supervisors" :value="$previousSupervisor" />
                        <div class="mb-3 row">
                            <label class="form-label col-sm-3 col-form-label">{{ __('logbook::logbook.handovers.fields.outgoing') }}</label>
                            <div class="col-sm-9"><p class="form-control-plaintext">{{ request()->user()->fullName() }}</p></div>
                        </div>
                        <x-ui.form.select name="taken_over_by" :label="__('logbook::logbook.handovers.fields.incoming')" :options="$users" required />
                        <x-ui.form.textarea name="instructions" :label="__('logbook::logbook.handovers.fields.instructions')" rows="5" />
                        <x-ui.form.actions :cancel="route('logbook.days.show', $day)" />
                    </form>
                </x-ui.card>
            </div>

            <div class="col-xl-5">
                <x-ui.card :title="__('logbook::logbook.days.counters.operation_counters')" :subtitle="__('logbook::logbook.handovers.snapshot_hint')">
                    <x-ui.detail-list>
                        @foreach ($operationCounters as $code => $last)
                            <x-ui.detail :label="strtoupper($code)">{{ str_pad((string) $last, 3, '0', STR_PAD_LEFT) }}</x-ui.detail>
                        @endforeach
                    </x-ui.detail-list>
                </x-ui.card>

                <x-ui.card :title="__('logbook::logbook.days.counters.open_operations')">
                    <x-ui.detail-list>
                        @foreach ($openOperations as $code => $codes)
                            <x-ui.detail :label="strtoupper($code)">
                                @forelse ($codes as $operationCode)
                                    <span title="{{ $operationCode }}">{{ $number($operationCode) }}</span>@unless ($loop->last) / @endunless
                                @empty
                                    {{ __('logbook::logbook.days.counters.none') }}
                                @endforelse
                            </x-ui.detail>
                        @endforeach
                    </x-ui.detail-list>
                </x-ui.card>

                @if ($standingInstructions->isNotEmpty())
                    <x-ui.card :title="__('logbook::logbook.handovers.standing_instructions')">
                        <ul class="mb-0 ps-3">
                            @foreach ($standingInstructions as $instruction)
                                <li class="text-pre-line">{{ $instruction->body }}@if ($instruction->vessel) — <x-ui.vessel-link :vessel="$instruction->vessel" />@endif</li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
