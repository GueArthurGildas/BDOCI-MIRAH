<x-layouts.app :title="__('logbook::logbook.entries.rectify_title')">
    <x-ui.page-header :title="__('logbook::logbook.entries.rectify_title')" :breadcrumbs="[__('logbook::logbook.days.title') => route('logbook.days.index'), $entry->day->day->isoFormat('L') => route('logbook.days.show', $entry->day)]" />

    <div class="page-body">
        <x-ui.card :title="__('logbook::logbook.entries.original')">
            <p class="m-b-5 text-pre-line">{{ $entry->content }}</p>
            <small class="text-muted">{{ $entry->occurred_at->isoFormat('L LT') }} · {{ $entry->channel->label }}</small>
        </x-ui.card>

        <x-ui.card :title="__('logbook::logbook.entries.rectify_title')" :subtitle="__('logbook::logbook.entries.rectify_hint')">
            <form method="POST" action="{{ route('logbook.entries.rectify.store', $entry) }}">
                @csrf
                <x-ui.form.textarea name="content" :label="__('logbook::logbook.entries.fields.content')" rows="4" required />
                <x-ui.form.actions :cancel="route('logbook.days.show', $entry->day)" />
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
