@php
    $isClosed = in_array($operation->status, [App\Enums\OperationStatus::Closed, App\Enums\OperationStatus::Archived], true);
@endphp
<x-layouts.app :title="$operation->code">
    <x-ui.page-header :title="$operation->code" :subtitle="$operation->name" :breadcrumbs="[__('operations::operations.title') => route('operations.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="__('operations::operations.details_title')">
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('operations::operations.fields.category')">{{ __($operation->category->code->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.status')"><x-ui.status-badge :status="$operation->status" /></x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.started_at')">{{ $operation->started_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.ended_at')">{{ $operation->ended_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.origin')">{{ $operation->origin }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.summary')"><span class="text-pre-line">{{ $operation->summary }}</span></x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.result')"><span class="text-pre-line">{{ $operation->result }}</span></x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.created_by')">{{ $operation->creator?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.closed_by')">{{ $operation->closer?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('operations::operations.fields.vessels')">
                            @foreach ($operation->vessels as $vessel)
                                <div><x-ui.vessel-link :vessel="$vessel" /></div>
                            @endforeach
                        </x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                @if ($operation->context)
                    <x-ui.card :title="__('operations::operations.context_title')">
                        <x-ui.detail-list>
                            <x-ui.detail :label="__('operations::operations.fields.first_event')"><span class="text-pre-line">{{ $operation->context->first_event }}</span></x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.temperature')">{{ $operation->context->temperature }}</x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.pressure')">{{ $operation->context->pressure }}</x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.visibility')">{{ $operation->context->visibility }}</x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.cloud_cover')">{{ $operation->context->cloud_cover }}</x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.wind_beaufort')">{{ $operation->context->wind_beaufort }}</x-ui.detail>
                            <x-ui.detail :label="__('operations::operations.fields.sea_state')">{{ $operation->context->sea_state }}</x-ui.detail>
                        </x-ui.detail-list>
                    </x-ui.card>
                @endif

                <x-ui.card :title="__('operations::operations.events_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('operations::operations.fields.occurred_at') }}</th>
                            <th>{{ __('operations::operations.fields.body') }}</th>
                        </x-slot:head>
                        @forelse ($operation->events as $event)
                            <tr>
                                <td class="text-nowrap">{{ $event->occurred_at->isoFormat('L LT') }}</td>
                                <td class="text-wrap text-pre-line">{{ $event->body }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                @if ($operation->logbookEntries->isNotEmpty())
                    <x-ui.card :title="__('operations::operations.logbook_title')" flush>
                        <x-ui.table>
                            <x-slot:head>
                                <th>{{ __('operations::operations.fields.occurred_at') }}</th>
                                <th>{{ __('operations::operations.fields.channel') }}</th>
                                <th>{{ __('operations::operations.fields.body') }}</th>
                            </x-slot:head>
                            @foreach ($operation->logbookEntries as $entry)
                                <tr>
                                    <td class="text-nowrap">{{ $entry->occurred_at->isoFormat('L LT') }}</td>
                                    <td>{{ $entry->channel->label }}</td>
                                    <td class="text-wrap text-pre-line">{{ $entry->content }}</td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </x-ui.card>
                @endif
            </div>

            <div class="col-xl-5">
                @unless ($isClosed)
                    @can(App\Support\Authorization\Permission::OperationsCreate->value)
                        <x-ui.card :title="__('operations::operations.add_event')">
                            <form method="POST" action="{{ route('operations.events.store', $operation) }}">
                                @csrf
                                <x-ui.form.input name="occurred_at" type="datetime-local" :label="__('operations::operations.fields.occurred_at')" :value="now()->format('Y-m-d\TH:i')" />
                                <x-ui.form.textarea name="body" :label="__('operations::operations.fields.body')" rows="3" required />
                                <x-ui.form.actions :cancel="route('operations.show', $operation)" />
                            </form>
                        </x-ui.card>
                    @endcan
                @endunless

                <x-ui.card :title="__('operations::operations.instructions_title')">
                    @forelse ($operation->instructions as $instruction)
                        <div class="m-b-15">
                            <p class="m-b-5 text-pre-line">{{ $instruction->body }}</p>
                            <small class="text-muted">{{ $instruction->giver?->name }} · {{ $instruction->created_at->isoFormat('L LT') }}</small>
                        </div>
                    @empty
                        <p class="text-muted m-0">{{ __('operations::operations.no_instruction') }}</p>
                    @endforelse

                    @unless ($isClosed)
                        @can(App\Support\Authorization\Permission::OperationsManage->value)
                            <hr>
                            <form method="POST" action="{{ route('operations.instructions.store', $operation) }}">
                                @csrf
                                <x-ui.form.textarea name="body" :label="__('operations::operations.fields.body')" rows="3" required />
                                <x-ui.form.actions :cancel="route('operations.show', $operation)" />
                            </form>
                        @endcan
                    @endunless
                </x-ui.card>

                @unless ($isClosed)
                    @can(App\Support\Authorization\Permission::OperationsManage->value)
                        <x-ui.card :title="__('operations::operations.follow_up_title')">
                            <form method="POST" action="{{ route('operations.update', $operation) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.form.select name="status" :label="__('operations::operations.fields.status')" :options="[App\Enums\OperationStatus::Open->value => __(App\Enums\OperationStatus::Open->label()), App\Enums\OperationStatus::InProgress->value => __(App\Enums\OperationStatus::InProgress->label())]" :value="$operation->status" required />
                                <x-ui.form.input name="origin" :label="__('operations::operations.fields.origin')" :value="$operation->origin" />
                                <x-ui.form.textarea name="summary" :label="__('operations::operations.fields.summary')" :value="$operation->summary" rows="3" />
                                <x-ui.form.actions :cancel="route('operations.show', $operation)" />
                            </form>
                        </x-ui.card>

                        <x-ui.card :title="__('operations::operations.close_title')">
                            <form data-confirm="{{ __('operations::confirm.close') }}" data-confirm-label="{{ __('operations::confirm.close_yes') }}" method="POST" action="{{ route('operations.close', $operation) }}">
                                @csrf
                                <x-ui.form.textarea name="result" :label="__('operations::operations.fields.result')" rows="3" required />
                                <div class="row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-success"><i class="feather icon-check"></i> {{ __('operations::operations.close') }}</button>
                                    </div>
                                </div>
                            </form>
                        </x-ui.card>
                    @endcan
                @endunless

                <x-ui.audit-history :subject="$operation" />
            </div>
        </div>
    </div>
</x-layouts.app>
