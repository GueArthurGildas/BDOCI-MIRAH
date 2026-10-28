<?php

namespace App\Models;

use App\Enums\LicenseOrigin;
use App\Enums\LicenseRecordStatus;
use App\Enums\LicenseSegment;
use App\Enums\LicenseStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable(['vessel_id', 'number', 'segment', 'issued_on', 'expires_on', 'status', 'entered_by', 'origin'])]
class License extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'segment' => LicenseSegment::class,
            'issued_on' => 'date',
            'expires_on' => 'date',
            'status' => LicenseRecordStatus::class,
            'origin' => LicenseOrigin::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'license_zone')->orderBy('name');
    }

    public function gears(): BelongsToMany
    {
        return $this->belongsToMany(Gear::class, 'gear_license')->orderBy('label');
    }

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'license_species')->orderBy('name');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(LicenseCondition::class)->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LicenseDocument::class)->latest('id');
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(LicenseSuspension::class)->latest('started_on')->latest('id');
    }

    public function revocation(): HasOne
    {
        return $this->hasOne(LicenseRevocation::class);
    }

    protected function standing(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->status === LicenseRecordStatus::Revoked => LicenseStatus::Revoked,
            $this->status === LicenseRecordStatus::Suspended => LicenseStatus::Suspended,
            $this->expires_on->endOfDay()->isPast() => LicenseStatus::Expired,
            $this->expires_on->lte(self::expiryWarningDate()) => LicenseStatus::Expiring,
            default => LicenseStatus::Valid,
        });
    }

    public function daysUntilExpiry(): int
    {
        return (int) today()->diffInDays($this->expires_on, false);
    }

    public function scopeWithStatus(Builder $query, ?LicenseStatus $status): void
    {
        match ($status) {
            LicenseStatus::Revoked => $query->where('status', LicenseRecordStatus::Revoked->value),
            LicenseStatus::Suspended => $query->where('status', LicenseRecordStatus::Suspended->value),
            LicenseStatus::Expired => $query->whereNotIn('status', self::blockedStatuses())->where('expires_on', '<', today()),
            LicenseStatus::Expiring => $query->whereNotIn('status', self::blockedStatuses())->whereBetween('expires_on', [today(), self::expiryWarningDate()]),
            LicenseStatus::Valid => $query->whereNotIn('status', self::blockedStatuses())->where('expires_on', '>', self::expiryWarningDate()),
            null => null,
        };
    }

    public function scopeExpiringBetween(Builder $query, int $afterDays, int $upToDays): void
    {
        $query->whereNotIn('status', self::blockedStatuses())
            ->where('expires_on', '>=', today()->addDays($afterDays))
            ->where('expires_on', '<=', today()->addDays($upToDays));
    }

    public function scopeAuthorized(Builder $query): void
    {
        $query->where('status', LicenseRecordStatus::Active->value)->where('expires_on', '>=', today());
    }

    /** @return array<int, string> */
    public static function blockedStatuses(): array
    {
        return [LicenseRecordStatus::Suspended->value, LicenseRecordStatus::Revoked->value];
    }

    private static function expiryWarningDate(): Carbon
    {
        return today()->addDays(config('surveillance.license_expiry_warning_days'));
    }
}
