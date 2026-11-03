<div class="gs-quick">
    <div class="gs-quick-head">
        <span class="gs-modal-icon"><i class="feather {{ $data->widget->icon }}"></i></span>
        <div>
            <h5>{{ $data->widget->title() }}</h5>
            <small>{{ $data->widget->kindLabel() }}</small>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
    </div>

    <p class="gs-widget-explanation">
        <strong>{{ __('dashboard::dashboard.details.meaning') }}</strong><br>
        {{ $data->widget->explanation() }}
    </p>

    @if ($data->value !== null)
        <div class="gs-tiles">
            <div>
                <small>{{ __('dashboard::dashboard.details.current_value') }}</small>
                <strong class="gs-widget-value">{{ Number::format($data->value) }}</strong>
            </div>
        </div>
    @endif

    @if ($data->figures)
        <h6>{{ __('dashboard::dashboard.details.figures') }}</h6>
        <x-ui.table :paginate="false">
            <x-slot:head>
                <th>{{ __('dashboard::dashboard.details.category') }}</th>
                <th class="text-end">{{ __('dashboard::dashboard.details.count') }}</th>
                <th class="text-end">{{ __('dashboard::dashboard.details.share') }}</th>
            </x-slot:head>
            @foreach ($data->figures as $figure)
                <tr>
                    <td><span class="gs-figure-dot" style="background: {{ $figure['color'] }}"></span> {{ $figure['label'] }}</td>
                    <td class="text-end">{{ Number::format($figure['value']) }}</td>
                    <td class="text-end">{{ $data->total() > 0 ? Number::percentage($figure['value'] / $data->total() * 100, precision: 0) : '–' }}</td>
                </tr>
            @endforeach
            <tr>
                <th>{{ __('dashboard::dashboard.details.total') }}</th>
                <th class="text-end">{{ Number::format($data->total()) }}</th>
                <th></th>
            </tr>
        </x-ui.table>
    @endif

    @if ($sample->isNotEmpty())
        <h6>{{ __('dashboard::dashboard.details.sample', ['count' => $sample->count()]) }}</h6>
        <x-ui.table>
            <x-slot:head>
                <th>{{ __('dashboard::dashboard.columns.vessel') }}</th>
                <th>{{ __('dashboard::dashboard.columns.description') }}</th>
            </x-slot:head>
            @foreach ($sample as $row)
                <tr>
                    <td><x-ui.vessel-link :vessel="$row->vessel" /></td>
                    <td class="text-wrap">{{ $row->detail() }}</td>
                </tr>
            @endforeach
        </x-ui.table>
    @endif

    @if ($detailUrl)
        <div class="text-end mt-3">
            <a href="{{ $detailUrl }}" class="btn btn-primary">{{ __('dashboard::dashboard.details.see_more') }} <i class="feather icon-arrow-right"></i></a>
        </div>
    @endif
</div>
