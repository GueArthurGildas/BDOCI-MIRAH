<?php

namespace App\Support\Vms;

use App\Models\Vessel;

final class ThemisLink
{
    public function identifier(Vessel $vessel): ?string
    {
        return $vessel->imo_number ?: $vessel->call_sign ?: $vessel->registration_number;
    }

    public function url(Vessel $vessel): ?string
    {
        $template = config('surveillance.vms.themis_vessel_url');
        $identifier = $this->identifier($vessel);

        return $template && $identifier ? str_replace('{identifier}', rawurlencode($identifier), $template) : null;
    }
}
