@props(['config', 'height' => 280])

<div style="height: {{ $height }}px">
    <canvas data-chart='@json($config)'></canvas>
</div>
