@props(['current'])

<div class="gs-notions" role="note">
    <strong>{{ __('ui.notions.title') }}</strong>
    <ul>
        @foreach (['alert', 'communication', 'notification'] as $notion)
            <li @class(['is-current' => $notion === $current])>
                <span class="gs-notion-name">{{ __('ui.notions.'.$notion.'.name') }}</span> : {{ __('ui.notions.'.$notion.'.text') }}
            </li>
        @endforeach
    </ul>
</div>
