<?php

namespace Modules\Movements\Services;

use App\Enums\EezDeclarationType;
use App\Models\EezDeclaration;
use App\Models\EntryExitLink;

class EntryExitPairing
{
    public function pairingEntryFor(EezDeclaration $exit): ?EezDeclaration
    {
        if ($exit->vessel_id === null) {
            return null;
        }

        return EezDeclaration::query()
            ->where('vessel_id', $exit->vessel_id)
            ->where('type', EezDeclarationType::Entry)
            ->where('received_at', '<=', $exit->received_at)
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('entry_exit_links')->whereColumn('entry_exit_links.entry_declaration_id', 'eez_declarations.id'))
            ->latest('received_at')
            ->latest('id')
            ->first();
    }

    public function varianceKg(EezDeclaration $entry, EezDeclaration $exit): float
    {
        return round($exit->totalCatchKg() - $entry->totalCatchKg(), 2);
    }

    public function exceedsTolerance(EezDeclaration $entry, float $varianceKg): bool
    {
        if ($varianceKg == 0.0) {
            return false;
        }

        $reference = $entry->totalCatchKg();

        if ($reference <= 0.0) {
            return true;
        }

        return abs($varianceKg) / $reference * 100 > config('surveillance.eez_variance_tolerance_percent');
    }

    public function link(EezDeclaration $entry, EezDeclaration $exit): EntryExitLink
    {
        $variance = $this->varianceKg($entry, $exit);

        return EntryExitLink::create([
            'entry_declaration_id' => $entry->id,
            'exit_declaration_id' => $exit->id,
            'variance_kg' => $variance,
            'explanation' => $this->exceedsTolerance($entry, $variance) ? 'unexplained' : null,
        ]);
    }
}
