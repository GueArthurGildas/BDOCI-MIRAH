<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Models\Concerns\Audited;
use App\Support\Settings\SettingKey;
use App\Support\Settings\Settings;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['vessel_id', 'number', 'type', 'valid_from', 'valid_until', 'suspended_at'])]
class License extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => LicenseType::class,
            'valid_from' => 'date',
            'valid_until' => 'date',
            'suspended_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->suspended_at !== null => LicenseStatus::Suspended,
            $this->valid_until->endOfDay()->isPast() => LicenseStatus::Expired,
            $this->valid_until->lte(self::expiryWarningDate()) => LicenseStatus::Expiring,
            default => LicenseStatus::Valid,
        });
    }

    public function scopeWithStatus(Builder $query, ?LicenseStatus $status): void
    {
        match ($status) {
            LicenseStatus::Suspended => $query->whereNotNull('suspended_at'),
            LicenseStatus::Expired => $query->whereNull('suspended_at')->where('valid_until', '<', today()),
            LicenseStatus::Expiring => $query->whereNull('suspended_at')->whereBetween('valid_until', [today(), self::expiryWarningDate()]),
            LicenseStatus::Valid => $query->whereNull('suspended_at')->where('valid_until', '>', self::expiryWarningDate()),
            null => null,
        };
    }

    private static function expiryWarningDate(): Carbon
    {
        return today()->addDays(app(Settings::class)->int(SettingKey::LicenseExpiryWarningDays));
    }
}
