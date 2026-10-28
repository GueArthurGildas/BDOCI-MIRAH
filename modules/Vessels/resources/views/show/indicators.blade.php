<div class="row">
    <div class="col-xl-5">
        <x-ui.card :title="__('vessels::vessels.indicators.score_title')" :subtitle="__('vessels::vessels.indicators.score_hint')">
            <h2 class="m-t-0">
                <span class="label label-{{ App\Support\Vessels\VigilanceScore::color($score['level']) }}">{{ $score['total'] }} / {{ config('vigilance.score.maximum') }}</span>
                <small class="text-muted m-l-10">{{ __('vigilance.score.levels.'.$score['level']) }}</small>
            </h2>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('vessels::vessels.indicators.component') }}</th>
                    <th class="text-end">{{ __('vessels::vessels.indicators.occurrences') }}</th>
                    <th class="text-end">{{ __('vessels::vessels.indicators.points') }}</th>
                </x-slot:head>
                @foreach ($score['components'] as $component)
                    <tr @class(['text-muted' => $component['points'] === 0])>
                        <td>{{ __('vigilance.score.components.'.$component['key']) }}</td>
                        <td class="text-end">{{ $component['occurrences'] }}</td>
                        <td class="text-end f-w-600">{{ $component['points'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
    <div class="col-xl-7">
        <x-ui.card :title="__('vessels::vessels.indicators.title')" :subtitle="__('vessels::vessels.indicators.window')">
            <x-ui.detail-list>
                <x-ui.detail :label="__('vessels::vessels.indicators.silence_now')">{{ $indicators->silenceHours !== null ? __('vessels::vessels.indicators.hours', ['value' => Number::format($indicators->silenceHours, 1)]) : '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.silences_12_months')">{{ $indicators->silences12Months }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.days_before_expiry')">{{ $indicators->daysBeforeExpiry ?? '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.inspections')">{{ $indicators->inspections12Months }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.infractions')">{{ $indicators->infractions12Months }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.checks')">{{ $indicators->checks }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.catch_variance')">{{ $indicators->meanCatchVariancePercent !== null ? $indicators->meanCatchVariancePercent.' %' : '—' }}</x-ui.detail>
                <x-ui.detail :label="__('vessels::vessels.indicators.unanswered_messages')">{{ $indicators->unansweredMessages }}</x-ui.detail>
            </x-ui.detail-list>
        </x-ui.card>
    </div>
</div>
