@php
    $isClosed = $operation->isFinished();
@endphp
<x-layouts.app :title="$operation->code">
    <x-ui.page-header :title="$operation->code" :subtitle="$operation->name" :breadcrumbs="[__('operations::operations.title') => route('operations.index')]">
        <x-slot:actions>
            <x-ui.status-badge :status="$operation->status" />
            <a href="{{ route('operations.word', $operation) }}" class="btn btn-sm btn-outline-primary"><i class="feather icon-file-text"></i> {{ __('operations::operations.print.word') }}</a>
            <a href="{{ route('operations.pdf', $operation) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="feather icon-printer"></i> {{ __('operations::operations.print.pdf') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card data-guide="details" :title="__('operations::operations.context_title')" :subtitle="__('operations::operations.context_hint')">
                    @include('operations::partials.context', ['operation' => $operation])
                </x-ui.card>

                <x-ui.card data-guide="events" :title="__('operations::operations.events_title')" :subtitle="__('operations::operations.events_hint')" flush>
                    <x-ui.table :paginate="false">
                        <x-slot:head>
                            <th>{{ __('operations::operations.fields.when') }}</th>
                            <th>{{ __('operations::operations.fields.channel') }}</th>
                            <th>{{ __('operations::operations.fields.body') }}</th>
                        </x-slot:head>
                        @forelse ($operation->events as $event)
                            <tr>
                                <td class="text-nowrap">{{ $event->occurred_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{ $event->channel?->label }}
                                    @if ($event->direction)
                                        <div class="text-muted f-12">{{ __($event->direction->label()) }}</div>
                                    @endif
                                </td>
                                <td class="text-wrap">
                                    <span class="text-pre-line">{{ $event->body }}</span>
                                    @if ($event->is_order_or_decision)
                                        <span class="label label-danger">{{ __('operations::operations.fields.order_or_decision') }}</span>
                                    @endif
                                    @if ($event->author)
                                        <div class="text-muted f-12">{{ __('operations::operations.fields.author') }} : {{ $event->author->fullName() }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                @if ($isClosed)
                    <x-ui.card data-guide="closing" :title="__('operations::operations.closing_title')">
                        @include('operations::partials.closing', ['operation' => $operation])
                    </x-ui.card>
                @endif

                <x-ui.card data-guide="documents" :title="__('operations::operations.documents_title')" :subtitle="__('operations::operations.documents_hint')">
                    @forelse ($documents as $document)
                        <div class="m-b-5"><i class="feather icon-paperclip"></i> <a href="{{ route('operations.documents.download', [$operation, $document]) }}">{{ $document->name }}</a> <span class="text-muted f-12">{{ $document->created_at?->isoFormat('L LT') }}</span></div>
                    @empty
                        <p class="text-muted mb-0">{{ __('operations::operations.no_document') }}</p>
                    @endforelse

                    @unless ($isClosed)
                        @can(App\Support\Authorization\Permission::OperationsCreate->value)
                            <hr>
                            <form method="POST" action="{{ route('operations.documents.store', $operation) }}" enctype="multipart/form-data">
                                @csrf
                                <x-ui.form.input name="file" type="file" :label="__('operations::operations.fields.file')" required />
                                <div class="row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-outline-primary"><i class="feather icon-paperclip"></i> {{ __('operations::operations.attach') }}</button>
                                    </div>
                                </div>
                            </form>
                        @endcan
                    @endunless
                </x-ui.card>

                <x-ui.card :title="__('operations::operations.logbook_title')" :subtitle="__('operations::operations.logbook_hint')" flush>
                    <x-ui.table :paginate="false">
                        <x-slot:head>
                            <th>{{ __('operations::operations.fields.when') }}</th>
                            <th>{{ __('operations::operations.fields.channel') }}</th>
                            <th>{{ __('operations::operations.fields.body') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($operation->logbookEntries as $entry)
                            <tr>
                                <td class="text-nowrap">{{ $entry->occurred_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $entry->channel->label }}</td>
                                <td class="text-wrap">
                                    @if ($entry->operation_milestone)
                                        <x-ui.status-badge :status="$entry->operation_milestone" />
                                    @endif
                                    <span class="text-pre-line">{{ $entry->content }}</span>
                                </td>
                                <td class="text-end"><a href="{{ route('logbook.days.show', $entry->logbook_day_id) }}" class="btn btn-outline-primary btn-mini">{{ __('operations::operations.open_in_logbook') }}</a></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="4" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @unless ($isClosed)
                    @can(App\Support\Authorization\Permission::OperationsCreate->value)
                        <x-ui.card data-guide="add_event" :title="__('operations::operations.add_event')" :subtitle="__('operations::operations.events_hint')">
                            <form method="POST" action="{{ route('operations.events.store', $operation) }}">
                                @csrf
                                <x-ui.form.input name="occurred_at" type="datetime-local" :label="__('operations::operations.fields.when')" :value="now()->format('Y-m-d\TH:i')" />
                                <x-ui.form.select name="channel_id" :label="__('operations::operations.fields.channel')" :options="$channels" required />
                                <x-ui.form.select name="direction" :label="__('operations::operations.fields.direction')" :options="App\Enums\LogbookDirection::options()" value="received" required />
                                <x-ui.form.textarea name="body" :label="__('operations::operations.fields.body')" rows="3" required />
                                <x-ui.form.checkbox name="is_order_or_decision" :label="__('operations::operations.fields.order_or_decision')" />
                                <x-ui.form.actions :cancel="route('operations.show', $operation)" />
                            </form>
                        </x-ui.card>
                    @endcan
                @endunless

                <x-ui.card data-guide="instructions" :title="__('operations::operations.instructions_title')">
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
                    @endcan

                    @can(App\Support\Authorization\Permission::OperationsClose->value)
                        <x-ui.card data-guide="close" :title="__('operations::operations.close_title')" :subtitle="__('operations::operations.close_hint')">
                            <form data-confirm="{{ __('operations::confirm.close') }}" data-confirm-label="{{ __('operations::confirm.close_yes') }}" method="POST" action="{{ route('operations.close', $operation) }}">
                                @csrf
                                <x-ui.form.textarea name="result" :label="__('operations::operations.fields.result')" rows="3" required />
                                <x-ui.form.textarea name="infractions_noted" :label="__('operations::operations.fields.infractions_noted')" rows="2" />
                                <x-ui.form.select name="channel_id" :label="__('operations::operations.fields.channel')" :options="$channels" />
                                <x-ui.form.select name="direction" :label="__('operations::operations.fields.direction')" :options="App\Enums\LogbookDirection::options()" value="received" />
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
