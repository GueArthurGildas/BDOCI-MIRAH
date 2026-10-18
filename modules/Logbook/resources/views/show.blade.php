<x-layouts.app :title="__('logbook::logbook.title')">
    <x-ui.page-header :title="__('logbook::logbook.title')" :subtitle="$observation->observed_at->isoFormat('LLLL')" :breadcrumbs="[__('logbook::logbook.title') => route('logbook.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-7">
                <x-ui.card :title="__('logbook::logbook.fields.description')">
                    <p class="m-b-20 text-pre-line">{{ $observation->description }}</p>

                    <x-ui.detail-list>
                        <x-ui.detail :label="__('logbook::logbook.fields.level')"><x-ui.status-badge :status="$observation->level" /></x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.status')"><x-ui.status-badge :status="$observation->status" /></x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.vessel')">
                            @if ($observation->vessel)
                                <x-ui.vessel-link :vessel="$observation->vessel" />
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.zone')">{{ $observation->zone?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.latitude')">{{ $observation->latitude ? Number::format($observation->latitude, 4) : '' }}</x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.longitude')">{{ $observation->longitude ? Number::format($observation->longitude, 4) : '' }}</x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.author')">{{ $observation->author->name }}</x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.team')">{{ $observation->team?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('logbook::logbook.fields.closed_at')">{{ $observation->closed_at?->isoFormat('L LT') }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>
            </div>

            <div class="col-xl-5">
                <x-ui.card :title="__('logbook::logbook.follow_up_title')">
                    @can(App\Support\Authorization\Permission::LogbookManage->value)
                        <form method="POST" action="{{ route('logbook.update', $observation) }}">
                            @csrf
                            @method('PATCH')
                            <x-ui.form.select name="status" :label="__('logbook::logbook.fields.status')" :options="App\Enums\ObservationStatus::options()" :value="$observation->status" required />
                            <x-ui.form.textarea name="follow_up" :label="__('logbook::logbook.fields.follow_up')" :value="$observation->follow_up" />
                            <x-ui.form.actions :cancel="route('logbook.index')" />
                        </form>
                    @else
                        <p class="m-0 text-pre-line">{{ $observation->follow_up ?? '—' }}</p>
                    @endcan
                </x-ui.card>

                <x-ui.card :title="__('logbook::logbook.history_title')">
                    <ul class="list-unstyled m-0">
                        @forelse ($observation->activitiesAsSubject->sortByDesc('created_at') as $activity)
                            <li class="m-b-10">
                                <i class="feather icon-clock text-muted m-r-5"></i>
                                {{ __('logbook::logbook.history.'.($activity->event === 'created' ? 'created' : 'updated'), ['user' => $activity->causer?->name ?? __('logbook::logbook.history.system')]) }}
                                <span class="text-muted f-12">— {{ $activity->created_at->isoFormat('L LT') }}</span>
                            </li>
                        @empty
                            <li class="text-muted">{{ __('logbook::logbook.history.empty') }}</li>
                        @endforelse
                    </ul>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
