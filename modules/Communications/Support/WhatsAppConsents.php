<?php

namespace Modules\Communications\Support;

use App\Models\ReferenceList;
use App\Models\ReferenceListValue;

class WhatsAppConsents
{
    public function normalize(string $number): string
    {
        return preg_replace('/[^\d+]/', '', $number);
    }

    public function isGranted(string $number): bool
    {
        return $this->values()->where('code', $this->normalize($number))->where('is_active', true)->exists();
    }

    public function grant(string $number, ?string $label = null): ReferenceListValue
    {
        $code = $this->normalize($number);

        return ReferenceListValue::updateOrCreate(
            ['reference_list_id' => $this->list()->id, 'code' => $code],
            ['label' => $label ?: $code, 'is_active' => true],
        );
    }

    public function revoke(string $number): ReferenceListValue
    {
        $value = $this->values()->where('code', $this->normalize($number))->firstOrFail();
        $value->update(['is_active' => false]);

        return $value;
    }

    public function granted()
    {
        return $this->values()->where('is_active', true)->orderBy('label')->get();
    }

    private function values()
    {
        return ReferenceListValue::where('reference_list_id', $this->list()->id);
    }

    private function list(): ReferenceList
    {
        return ReferenceList::firstOrCreate(
            ['code' => config('communications.whatsapp_consent_list')],
            ['label' => 'WhatsApp consents'],
        );
    }
}
