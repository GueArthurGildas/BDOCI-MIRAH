<ul class="gs-figures">
    @foreach ($figures as $figure)
        <li>
            <span class="gs-figure-dot" style="background: {{ $figure['color'] }}"></span>
            <span class="gs-figure-label">{{ $figure['label'] }}</span>
            <strong>{{ Number::format($figure['value']) }}</strong>
        </li>
    @endforeach
</ul>
