<?php

namespace App\Support\Connectors\Sources;

use App\Enums\SourceSystem;

class NenoConnector extends VesselFeedConnector
{
    public function code(): string
    {
        return 'neno';
    }

    protected function system(): SourceSystem
    {
        return SourceSystem::Neno;
    }
}
