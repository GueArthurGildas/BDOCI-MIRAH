@props(['markers' => [], 'zones' => [], 'height' => '480px'])

<div {{ $attributes->class('map-canvas') }} style="height: {{ $height }}" data-map='@json(['markers' => $markers, 'zones' => $zones])'></div>
