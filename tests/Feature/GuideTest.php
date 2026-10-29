<?php

namespace Tests\Feature;

use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_guided_page_is_a_real_route_with_an_intro_and_texts_in_both_languages(): void
    {
        foreach (config('guide.pages') as $route => $steps) {
            $key = str_replace('.', '_', $route);
            $this->assertTrue(Route::has($route), "{$route} is not a route");

            foreach (['fr', 'en'] as $locale) {
                $this->assertTrue(Lang::hasForLocale("guide.pages.{$key}.intro", $locale), "{$route} has no {$locale} intro");

                foreach (array_keys($steps) as $step) {
                    $found = Lang::hasForLocale("guide.pages.{$key}.steps.{$step}", $locale) || Lang::hasForLocale("guide.common.{$step}", $locale);
                    $this->assertTrue($found, "{$route} step {$step} has no {$locale} text");
                }
            }
        }
    }

    public function test_the_generic_steps_have_texts_in_both_languages(): void
    {
        foreach (['fr', 'en'] as $locale) {
            foreach (array_keys(config('guide.generic')) as $step) {
                $this->assertTrue(Lang::hasForLocale("guide.common.{$step}", $locale));
            }
        }
    }

    public function test_a_guided_page_embeds_its_tour_in_the_current_language(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('vessels.index'))
            ->assertOk()
            ->assertSee('id="gs-guide-data"', false)
            ->assertSee('data-gs-guide', false)
            ->assertSee(__('guide.pages.vessels_index.intro'), false);
    }

    public function test_a_page_without_a_tour_falls_back_to_the_generic_one(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('id="gs-guide-data"', false)
            ->assertSee(__('guide.generic_intro'), false);
    }
}
