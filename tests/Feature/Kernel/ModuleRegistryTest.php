<?php

namespace Tests\Feature\Kernel;

use App\Models\RegisteredModule;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_module_provider_is_declared_and_synchronized_once(): void
    {
        $directories = collect(glob(base_path('modules/*'), GLOB_ONLYDIR))->map(fn (string $path) => str(basename($path))->kebab()->toString());

        $this->artisan('modules:sync')->assertSuccessful();
        $this->artisan('modules:sync')->assertSuccessful();

        $this->assertEqualsCanonicalizing($directories->all(), RegisteredModule::pluck('code')->all());
        $this->assertSame($directories->count(), RegisteredModule::count());
    }

    public function test_modules_depend_only_on_the_core(): void
    {
        $this->assertSame([], collect(app(ModuleRegistry::class)->declared())->filter(fn (array $module) => $module['dependencies'] !== [])->keys()->all());
    }

    public function test_an_undeclared_module_code_is_registered_on_first_use(): void
    {
        $id = app(ModuleRegistry::class)->idFor('future-module');

        $this->assertSame('future-module', RegisteredModule::findOrFail($id)->code);
    }
}
