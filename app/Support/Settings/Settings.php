<?php

namespace App\Support\Settings;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class Settings
{
    private const CACHE_KEY = 'settings.overrides';

    /** @var array<string, int|bool> */
    private array $baselines = [];

    /** @return Collection<string, SettingDefinition> */
    public function definitions(): Collection
    {
        return collect([
            new SettingDefinition('notifications.poll_seconds', 'surveillance.notification_poll_seconds', SettingType::Integer, 'browser', 10, 600),
            new SettingDefinition('notifications.toast_seconds', 'surveillance.notification_toast_seconds', SettingType::Integer, 'browser', 3, 30),
            new SettingDefinition('notifications.max_toasts', 'surveillance.notification_max_toasts', SettingType::Integer, 'browser', 1, 5),
            new SettingDefinition('alerts.beacons_silent.enabled', 'surveillance.alerts.beacons_silent.enabled', SettingType::Boolean, 'beacons'),
            new SettingDefinition('alerts.beacons_silent.cooldown_hours', 'surveillance.alerts.beacons_silent.cooldown_hours', SettingType::Integer, 'beacons', 1, 72),
            new SettingDefinition('alerts.licenses_expiring.enabled', 'surveillance.alerts.licenses_expiring.enabled', SettingType::Boolean, 'licenses'),
            new SettingDefinition('alerts.licenses_expiring.warning_days', 'surveillance.license_expiry_warning_days', SettingType::Integer, 'licenses', 1, 365),
            new SettingDefinition('alerts.licenses_expiring.cooldown_hours', 'surveillance.alerts.licenses_expiring.cooldown_hours', SettingType::Integer, 'licenses', 1, 168),
            new SettingDefinition('alerts.eez_declarations.enabled', 'surveillance.alerts.eez_declarations.enabled', SettingType::Boolean, 'eez'),
            new SettingDefinition('alerts.eez_declarations.position_window_hours', 'surveillance.eez_position_window_hours', SettingType::Integer, 'eez', 1, 48),
            new SettingDefinition('alerts.eez_declarations.cooldown_hours', 'surveillance.alerts.eez_declarations.cooldown_hours', SettingType::Integer, 'eez', 1, 168),
            new SettingDefinition('eez.variance_tolerance_percent', 'surveillance.eez_variance_tolerance_percent', SettingType::Integer, 'eez', 0, 100),
            new SettingDefinition('watch.shift_start_hour', 'surveillance.watch.shift_start_hour', SettingType::Integer, 'watch', 0, 23),
            new SettingDefinition('watch.shift_end_hour', 'surveillance.watch.shift_end_hour', SettingType::Integer, 'watch', 1, 23),
            new SettingDefinition('watch.teams_count', 'surveillance.watch.teams_count', SettingType::Integer, 'watch', 1, 10),
            new SettingDefinition('watch.check_threshold', 'surveillance.watch.check_threshold', SettingType::Integer, 'watch', 1, 20),
            new SettingDefinition('alerts.scheduled_reports.enabled', 'surveillance.alerts.scheduled_reports.enabled', SettingType::Boolean, 'reports'),
            new SettingDefinition('archive.after_years', 'surveillance.archive_after_years', SettingType::Integer, 'reports', 1, 50),
            new SettingDefinition('connectors.stale_after_hours', 'surveillance.connectors.stale_after_hours', SettingType::Integer, 'connectors', 1, 720),
            new SettingDefinition('connectors.catch_variance_threshold_percent', 'surveillance.connectors.catch_variance_threshold_percent', SettingType::Integer, 'connectors', 0, 100),
            new SettingDefinition('alerts.licenses_expiring.second_warning_days', 'surveillance.license_expiry_second_warning_days', SettingType::Integer, 'licenses', 1, 730),
            new SettingDefinition('alerts.licenses_expiring.third_warning_days', 'surveillance.license_expiry_third_warning_days', SettingType::Integer, 'licenses', 1, 730),
            new SettingDefinition('infractions.repeat_threshold', 'surveillance.infractions.repeat_threshold', SettingType::Integer, 'infractions', 2, 20),
            new SettingDefinition('missions.team_lead_authorizes.maritime', 'surveillance.missions.team_lead_authorizes.maritime', SettingType::Boolean, 'missions'),
            new SettingDefinition('missions.team_lead_authorizes.lagoon', 'surveillance.missions.team_lead_authorizes.lagoon', SettingType::Boolean, 'missions'),
            new SettingDefinition('missions.team_lead_authorizes.continental', 'surveillance.missions.team_lead_authorizes.continental', SettingType::Boolean, 'missions'),
            new SettingDefinition('missions.team_lead_authorizes.port', 'surveillance.missions.team_lead_authorizes.port', SettingType::Boolean, 'missions'),
            new SettingDefinition('missions.team_lead_authorizes.aerial_drone', 'surveillance.missions.team_lead_authorizes.aerial_drone', SettingType::Boolean, 'missions'),
        ])->keyBy(fn (SettingDefinition $definition) => $definition->field());
    }

    public function apply(): void
    {
        $stored = $this->stored();

        foreach ($this->definitions() as $definition) {
            $this->baselines[$definition->key] ??= config($definition->configKey);

            config([$definition->configKey => array_key_exists($definition->key, $stored)
                ? $definition->cast($stored[$definition->key])
                : $this->baselines[$definition->key]]);
        }
    }

    public function value(SettingDefinition $definition): int|bool
    {
        return config($definition->configKey);
    }

    public function baseline(SettingDefinition $definition): int|bool
    {
        return $this->baselines[$definition->key];
    }

    public function isCustomized(SettingDefinition $definition): bool
    {
        return array_key_exists($definition->key, $this->stored());
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->definitions()->map(fn (SettingDefinition $definition) => $definition->rules())->all();
    }

    /** @param array<string, mixed> $values keyed by form field */
    public function save(array $values, User $user): void
    {
        foreach ($this->definitions() as $field => $definition) {
            $value = $definition->cast($values[$field]);

            if ($value === $this->baselines[$definition->key]) {
                Setting::where('key', $definition->key)->first()?->delete();

                continue;
            }

            $setting = Setting::firstOrNew(['key' => $definition->key]);
            $setting->fill(['value' => (string) (int) $value, 'updated_by' => $user->id])->save();
        }

        $this->refresh();
    }

    public function reset(): void
    {
        Setting::all()->each->delete();

        $this->refresh();
    }

    private function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->apply();
    }

    /** @return array<string, string> */
    private function stored(): array
    {
        try {
            $cached = Cache::get(self::CACHE_KEY);

            if ($cached !== null) {
                return $cached;
            }

            $stored = Schema::hasTable('settings') ? Setting::pluck('value', 'key')->all() : [];
            Cache::forever(self::CACHE_KEY, $stored);

            return $stored;
        } catch (Throwable) {
            return [];
        }
    }
}
