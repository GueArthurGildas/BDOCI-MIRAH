<?php

namespace App\Support\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Settings
{
    private const CACHE_KEY = 'settings.all';

    public function get(SettingKey $key): mixed
    {
        return $this->stored()[$key->value] ?? $key->default();
    }

    public function int(SettingKey $key): int
    {
        return (int) $this->get($key);
    }

    public function float(SettingKey $key): float
    {
        return (float) $this->get($key);
    }

    public function string(SettingKey $key): string
    {
        return (string) $this->get($key);
    }

    public function aiActionEnabled(string $actionCode): bool
    {
        return ($this->stored()["ai.action.{$actionCode}"] ?? '0') === '1';
    }

    public function setAiActionEnabled(string $actionCode, bool $enabled): void
    {
        $this->write("ai.action.{$actionCode}", $enabled ? '1' : '0', 'ai_action_toggled');
    }

    public function set(SettingKey $key, mixed $value): void
    {
        $this->write($key->value, (string) $value, 'setting_changed', $key->default());
    }

    private function write(string $key, string $value, string $event, mixed $default = null): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $previous = $setting->exists ? $setting->value : $default;
        $setting->value = $value;
        $setting->save();

        Cache::forget(self::CACHE_KEY);

        activity('administration')
            ->event($event)
            ->withProperties(['key' => $key, 'old' => $previous, 'new' => $value])
            ->log($event);
    }

    /** @return array<string, string> */
    private function stored(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());
    }
}
