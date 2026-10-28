<?php

namespace App\Support\Connectors\Sources;

use App\Enums\SourceSystem;

class ThemisConnector extends VesselFeedConnector
{
    public function code(): string
    {
        return 'vms-themis';
    }

    protected function system(): SourceSystem
    {
        return SourceSystem::Themis;
    }
}
