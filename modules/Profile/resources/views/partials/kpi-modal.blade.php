<div class="modal fade gs-modal gs-form-modal" id="kpi-{{ $key }}" tabindex="-1" aria-hidden="true" aria-labelledby="kpi-{{ $key }}-title">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kpi-{{ $key }}-title">{{ __('profile::profile.cards.'.$key) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted m-b-10">{{ __('profile::profile.recent.hint', ['count' => Modules\Profile\Support\KpiRecent::LIMIT]) }}</p>
                @forelse ($recent['items'] as $item)
                    <div class="gs-recent-item">
                        <span class="label label-default">{{ __($item['kind']) }}</span>
                        <div class="gs-recent-text">
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}">{{ $item['text'] }}</a>
                            @else
                                {{ $item['text'] }}
                            @endif
                            @if ($item['vessel'])
                                <small class="text-muted">· {{ $item['vessel'] }}</small>
                            @endif
                        </div>
                        <small class="text-muted text-nowrap">{{ $item['at']->isoFormat('L LT') }}</small>
                    </div>
                @empty
                    <p class="text-muted">{{ __('profile::profile.empty') }}</p>
                @endforelse
            </div>
            @if ($recent['more'] !== [])
                <div class="modal-footer">
                    @foreach ($recent['more'] as $link)
                        <a href="{{ $link['url'] }}" class="btn btn-primary btn-sm">{{ __($link['label']) }} <i class="feather icon-arrow-right"></i></a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
