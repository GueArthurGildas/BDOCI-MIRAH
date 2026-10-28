<?php

namespace App\Support\Health;

use App\Enums\DataSourceStatus;
use App\Models\DataSource;
use Illuminate\Support\Collection;

final class SystemHealth
{
    /** @return Collection<int, DataSource> */
    public function affectedSources(): Collection
    {
        return DataSource::query()
            ->withIssueCounts()
            ->orderBy('name')
            ->get()
            ->filter(fn (DataSource $source) => $source->status !== DataSourceStatus::Operational
                || $source->open_errors_count > 0
                || $source->pending_rejections_count > 0)
            ->values();
    }

    /** @param Collection<int, DataSource> $sources */
    public function isCritical(Collection $sources): bool
    {
        return $sources->contains(fn (DataSource $source) => $source->status === DataSourceStatus::Down);
    }
}
