<x-layouts.app :title="__('watch::watch.instructions.title')">
    <x-ui.page-header :title="__('watch::watch.instructions.title')" :subtitle="__('watch::watch.instructions.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('watch::watch.instructions.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('watch::watch.fields.vessel') }}</th>
                            <th>{{ __('watch::watch.fields.body') }}</th>
                            <th>{{ __('watch::watch.fields.started_on') }}</th>
                            <th>{{ __('watch::watch.fields.ended_on') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($instructions as $instruction)
                            <tr>
                                <td>@if ($instruction->vessel)<x-ui.vessel-link :vessel="$instruction->vessel" />@else<span class="text-muted">{{ __('watch::watch.instructions.general') }}</span>@endif</td>
                                <td class="text-wrap text-pre-line">{{ $instruction->body }}</td>
                                <td class="text-nowrap">{{ $instruction->started_on->isoFormat('L') }}</td>
                                <td class="text-nowrap">{{ $instruction->ended_on?->isoFormat('L') }}</td>
                                <td class="text-end">
                                    @can(App\Support\Authorization\Permission::WatchManage->value)
                                        @if ($instruction->ended_on === null)
                                            <form data-confirm="{{ __('watch::confirm.end_instruction') }}" data-confirm-label="{{ __('watch::confirm.end_instruction_yes') }}" method="POST" action="{{ route('watch.instructions.end', $instruction) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-mini">{{ __('watch::watch.instructions.end') }}</button>
                                            </form>
                                        @endif
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
                @can(App\Support\Authorization\Permission::WatchManage->value)
                    <x-ui.card :title="__('watch::watch.instructions.new')">
                        <form method="POST" action="{{ route('watch.instructions.store') }}">
                            @csrf
                            <x-ui.form.select name="vessel_id" :label="__('watch::watch.fields.vessel')" :options="$vessels" :placeholder="__('watch::watch.instructions.general')" />
                            <x-ui.form.textarea name="body" :label="__('watch::watch.fields.body')" rows="3" required />
                            <x-ui.form.input name="started_on" type="date" :label="__('watch::watch.fields.started_on')" :value="today()->toDateString()" />
                            <x-ui.form.input name="ended_on" type="date" :label="__('watch::watch.fields.ended_on')" />
                            <x-ui.form.actions :cancel="route('watch.instructions.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>
