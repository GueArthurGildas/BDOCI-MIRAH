<?php

namespace Tests\Feature;

use App\Models\PermissionGroup;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class TranslationCompletenessTest extends TestCase
{
    use RefreshDatabase;

    private const LOCALES = ['fr', 'en'];

    public function test_every_enum_label_is_translated_in_both_languages(): void
    {
        $missing = [];

        foreach (glob(app_path('Enums/*.php')) as $file) {
            $class = 'App\\Enums\\'.basename($file, '.php');

            if (! enum_exists($class)) {
                continue;
            }

            foreach ($class::cases() as $case) {
                if (! method_exists($case, 'label')) {
                    continue;
                }

                foreach (self::LOCALES as $locale) {
                    if (! Lang::has($case->label(), $locale, false)) {
                        $missing[] = "{$locale}: {$case->label()}";
                    }
                }
            }
        }

        $this->assertSame([], $missing, 'Enum labels without translation');
    }

    public function test_every_system_permission_group_has_a_label_in_both_languages(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $missing = [];

        foreach (PermissionGroup::where('is_system', true)->get() as $group) {
            foreach (self::LOCALES as $locale) {
                $key = "administration::groups.system.{$group->code}.label";

                if (! Lang::has($key, $locale, false)) {
                    $missing[] = "{$locale}: {$key}";
                }
            }
        }

        $this->assertSame([], $missing, 'System groups without a label');
    }
}
