@props(['subject'])

<x-ui.card :title="__('ui.history.title')">
    <ul class="list-unstyled m-0">
        @forelse ($subject->activitiesAsSubject->sortByDesc('created_at') as $activity)
            <li class="m-b-10">
                <i class="feather icon-clock text-muted m-r-5"></i>
                {{ __($activity->event === 'created' ? 'ui.history.created' : 'ui.history.updated', ['user' => $activity->causer?->name ?? __('ui.history.system')]) }}
                <span class="text-muted f-12">— {{ $activity->created_at->isoFormat('L LT') }}</span>
            </li>
        @empty
            <li class="text-muted">{{ __('ui.history.empty') }}</li>
        @endforelse
    </ul>
</x-ui.card>
