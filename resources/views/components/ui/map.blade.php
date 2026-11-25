@props(['markers' => [], 'zones' => [], 'height' => '480px', 'create' => null])

<div {{ $attributes->class('map-canvas') }} style="height: {{ $height }}" data-map='@json(['markers' => $markers, 'zones' => $zones, 'create' => $create])'></div>
