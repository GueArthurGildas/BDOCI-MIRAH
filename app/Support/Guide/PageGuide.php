<?php

namespace App\Support\Guide;

use Illuminate\Support\Facades\Lang;

final class PageGuide
{
    public function current(): array
    {
        $route = request()->route()?->getName();
        $page = $route ? (config('guide.pages')[$route] ?? null) : null;
        $key = $route ? str_replace('.', '_', $route) : null;

        $steps = $page ?? config('guide.generic');
        $intro = $page && Lang::has("guide.pages.{$key}.intro") ? __("guide.pages.{$key}.intro") : __('guide.generic_intro');

        return [
            'intro' => ['title' => __('guide.intro_title'), 'text' => $intro],
            'steps' => collect($steps)
                ->map(fn (string $selector, string $step) => $this->step($key, $step, $selector))
                ->filter()
                ->values()
                ->all(),
            'labels' => [
                'next' => __('guide.next'),
                'previous' => __('guide.previous'),
                'done' => __('guide.done'),
                'close' => __('ui.close'),
                'progress' => __('guide.progress'),
            ],
        ];
    }

    private function step(?string $pageKey, string $step, string $selector): ?array
    {
        $own = "guide.pages.{$pageKey}.steps.{$step}";
        $source = $pageKey && Lang::has($own) ? $own : "guide.common.{$step}";

        if (! Lang::has($source)) {
            return null;
        }

        return ['selector' => $selector, ...__($source)];
    }
}
