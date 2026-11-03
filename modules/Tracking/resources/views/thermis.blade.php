<x-layouts.app :title="__('tracking::thermis.title')">
    <x-ui.page-header :title="__('tracking::thermis.title')" :subtitle="__('tracking::thermis.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('tracking::thermis.open_title')">
            @if ($baseUrl || $vesselUrlConfigured)
                <form method="GET" action="{{ route('thermis.index') }}">
                    <label for="thermis-vessels" class="form-label">{{ __('tracking::thermis.select_label') }}</label>
                    <select id="thermis-vessels" name="vessels[]" multiple size="8" class="form-select @error('vessels') is-invalid @enderror">
                        @foreach ($options as $id => $label)
                            <option value="{{ $id }}" @selected(in_array($id, $selectedIds, true))>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('tracking::thermis.select_hint') }}</div>
                    <div class="m-t-15 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="feather icon-search"></i> {{ __('tracking::thermis.prepare') }}</button>
                        @if ($baseUrl)
                            <a href="{{ $baseUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="feather icon-external-link"></i> {{ __('tracking::thermis.open_thermis') }}</a>
                        @endif
                    </div>
                </form>
            @else
                <p class="mb-0">{{ __('tracking::thermis.not_configured') }}</p>
            @endif
        </x-ui.card>

        @if ($targets->isNotEmpty())
            <x-ui.card :title="__('tracking::thermis.targets_title')" flush>
                <x-ui.table>
                    <x-slot:head>
                        <th>{{ __('tracking::thermis.vessel') }}</th>
                        <th class="text-end">{{ __('tracking::thermis.link') }}</th>
                    </x-slot:head>
                    @foreach ($targets as $target)
                        <tr>
                            <td><x-ui.vessel-link :vessel="$target['vessel']" /></td>
                            <td class="text-end">
                                @if ($target['url'])
                                    <a href="{{ $target['url'] }}" target="_blank" rel="noopener" class="btn btn-default btn-sm"><i class="feather icon-external-link"></i> {{ __('tracking::thermis.open_vessel') }}</a>
                                @else
                                    <span class="text-muted">{{ __('tracking::thermis.no_link') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @endif

        <x-ui.card :title="__('tracking::thermis.why_title')">
            <p class="mb-0">{{ __('tracking::thermis.why') }}</p>
        </x-ui.card>
    </div>
</x-layouts.app>
