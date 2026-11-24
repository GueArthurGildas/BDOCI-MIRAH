@inject('morningMenu', App\Support\Navigation\MorningMenu::class)
@php($morningSections = $morningMenu->sectionsFor(auth()->user()))

@if ($morningSections !== [])
    <div class="gs-mega dropdown">
        <button type="button" class="gs-mega-toggle dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" data-guide="morning-menu">
            <i class="feather icon-sunrise"></i>
            <span>{{ __('ui.morning_menu.title') }}</span>
        </button>
        <div class="dropdown-menu gs-mega-panel">
            <p class="gs-mega-hint">{{ __('ui.morning_menu.hint') }}</p>
            <div class="gs-mega-grid">
                @foreach ($morningSections as $index => $section)
                    <section class="gs-mega-section">
                        <h6><span class="gs-mega-step">{{ $index + 1 }}</span> {{ __('ui.morning_menu.sections.'.$section['key']) }}</h6>
                        <ul>
                            @foreach ($section['links'] as $link)
                                <li>
                                    <a href="{{ $link['url'] }}" @class(['is-active' => $link['active']]) data-gs-no-modal>
                                        <i class="feather {{ $link['icon'] }}"></i> {{ __($link['label']) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endif
