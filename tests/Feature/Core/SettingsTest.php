<?php

namespace Tests\Feature\Core;

use App\Support\Authorization\Role;
use App\Support\Settings\SettingKey;
use App\Support\Settings\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_comes_from_config_until_overridden(): void
    {
        $settings = app(Settings::class);

        $this->assertSame((int) config('surveillance.verifications_before_suspect'), $settings->int(SettingKey::VerificationsBeforeSuspect));

        $settings->set(SettingKey::VerificationsBeforeSuspect, 5);

        $this->assertSame(5, $settings->int(SettingKey::VerificationsBeforeSuspect));
        $this->assertTrue(Activity::where('description', 'setting_changed')->exists());
    }

    public function test_ai_actions_are_disabled_by_default(): void
    {
        $settings = app(Settings::class);

        $this->assertFalse($settings->aiActionEnabled('create_note'));
        $settings->setAiActionEnabled('create_note', true);
        $this->assertTrue($settings->aiActionEnabled('create_note'));
    }

    public function test_only_admin_can_open_the_settings_page(): void
    {
        $this->signInAs(Role::Director);
        $this->get(route('administration.settings.edit'))->assertForbidden();
    }

    public function test_admin_can_open_the_settings_page(): void
    {
        $this->signInAs(Role::Admin);
        $this->get(route('administration.settings.edit'))->assertOk();
    }
}
