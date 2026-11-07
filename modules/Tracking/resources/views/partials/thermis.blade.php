@if ($panel['configured'])
    <form method="GET" action="{{ $action }}">
        @if ($tab ?? null)
            <input type="hidden" name="tab" value="{{ $tab }}">
        @endif
        <label for="thermis-vessels" class="form-label">{{ __('tracking::thermis.select_label') }}</label>
        <select id="thermis-vessels" name="vessels[]" multiple size="8" class="form-select @error('vessels') is-invalid @enderror">
            @foreach ($panel['options'] as $id => $label)
                <option value="{{ $id }}" @selected(in_array($id, $panel['selectedIds'], true))>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('tracking::thermis.select_hint') }}</div>
        <div class="m-t-15 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><i class="feather icon-search"></i> {{ __('tracking::thermis.prepare') }}</button>
            @if ($panel['baseUrl'])
                <a href="{{ $panel['baseUrl'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="feather icon-external-link"></i> {{ __('tracking::thermis.open_thermis') }}</a>
            @endif
        </div>
    </form>

    @if ($panel['targets']->isNotEmpty())
        <div class="m-t-20">
            <h6>{{ __('tracking::thermis.targets_title') }}</h6>
            <ul class="list-unstyled mb-0">
                @foreach ($panel['targets'] as $target)
                    <li class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <x-ui.vessel-link :vessel="$target['vessel']" />
                        @if ($target['url'])
                            <a href="{{ $target['url'] }}" target="_blank" rel="noopener" class="btn btn-default btn-sm"><i class="feather icon-external-link"></i> {{ __('tracking::thermis.open_vessel') }}</a>
                        @else
                            <span class="text-muted">{{ __('tracking::thermis.no_link') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($panel['embedUrl'])
        <div class="gs-thermis-frame m-t-20">
            <iframe src="{{ $panel['embedUrl'] }}" title="{{ __('tracking::thermis.title') }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
            <p class="text-muted f-12 m-t-5 mb-0">{{ __('tracking::thermis.frame_hint') }}</p>
        </div>
    @endif
@else
    <p class="mb-0">{{ __('tracking::thermis.not_configured') }}</p>
@endif
