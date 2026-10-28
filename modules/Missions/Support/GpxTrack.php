<?php

namespace Modules\Missions\Support;

use Illuminate\Validation\ValidationException;

final readonly class GpxTrack
{
    private const MIN_POINTS = 2;

    /** @param array<int, array{0: float, 1: float}> $points */
    public function __construct(public array $points) {}

    public static function parse(string $xml): self
    {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($document === false) {
            throw ValidationException::withMessages(['file' => __('missions::missions.errors.gpx_invalid')]);
        }

        $document->registerXPathNamespace('g', 'http://www.topografix.com/GPX/1/1');
        $nodes = $document->xpath('//g:trkpt') ?: $document->xpath('//trkpt') ?: [];

        $points = array_map(fn (\SimpleXMLElement $node) => [(float) $node['lon'], (float) $node['lat']], $nodes);

        if (count($points) < self::MIN_POINTS) {
            throw ValidationException::withMessages(['file' => __('missions::missions.errors.gpx_too_short')]);
        }

        return new self($points);
    }

    public function wkt(): string
    {
        return 'LINESTRING('.implode(', ', array_map(fn (array $point) => $point[0].' '.$point[1], $this->points)).')';
    }
}
