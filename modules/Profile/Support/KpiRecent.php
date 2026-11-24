<?php

namespace Modules\Profile\Support;

use App\Enums\AlertCategory;
use App\Models\Alert;
use App\Models\InternalNote;
use App\Models\Observation;
use App\Models\Operation;
use App\Models\User;
use App\Support\Authorization\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class KpiRecent
{
    public const LIMIT = 5;

    private const TEXT_LIMIT = 120;

    /**
     * @param  array<string, mixed>  $space
     * @return array<string, array{items: Collection<int, array<string, mixed>>, more: array<int, array{url: string, label: string}>}>
     */
    public static function for(User $user, array $space): array
    {
        $canTrack = $user->can(Permission::TrackingView->value);
        $alerts = collect(AlertCategory::cases())->flatMap(fn (AlertCategory $category) => $space['alerts'][$category->value]);

        return [
            'messages' => [
                'items' => self::latest($space['messages']->map(fn (InternalNote $note) => self::note($note))),
                'more' => self::links($user, [[Permission::CommunicationsView, 'communications.notes.index', [], 'profile::profile.recent.all_messages']]),
            ],
            'urgencies' => [
                'items' => self::latest($space['urgentAlerts']->map(fn (Alert $alert) => self::alert($alert, $canTrack))->concat($space['urgentNotes']->map(fn (InternalNote $note) => self::note($note)))),
                'more' => self::links($user, [[Permission::TrackingView, 'tracking.alerts.index', ['category' => AlertCategory::Urgent->value], 'profile::profile.recent.all_alerts']]),
            ],
            'alerts' => [
                'items' => self::latest($alerts->map(fn (Alert $alert) => self::alert($alert, $canTrack))),
                'more' => self::links($user, [[Permission::TrackingView, 'tracking.alerts.index', [], 'profile::profile.recent.all_alerts']]),
            ],
            'pending' => [
                'items' => self::latest(collect()
                    ->concat($space['observations']->map(fn (Observation $observation) => [
                        'at' => $observation->observed_at,
                        'kind' => 'profile::profile.recent.kinds.observation',
                        'text' => Str::limit($observation->description, self::TEXT_LIMIT),
                        'vessel' => $observation->vessel?->name,
                        'url' => $user->can(Permission::LogbookView->value) ? route('logbook.show', $observation) : null,
                    ]))
                    ->concat($space['dueNotes']->map(fn (InternalNote $note) => self::note($note)))
                    ->concat($space['operations']->map(fn (Operation $operation) => [
                        'at' => $operation->started_at,
                        'kind' => 'profile::profile.recent.kinds.operation',
                        'text' => $operation->code,
                        'vessel' => null,
                        'url' => $user->can(Permission::OperationsView->value) ? route('operations.show', $operation) : null,
                    ]))),
                'more' => self::links($user, [
                    [Permission::LogbookView, 'logbook.index', ['pending' => 1], 'profile::profile.recent.all_observations'],
                    [Permission::OperationsView, 'operations.index', [], 'profile::profile.recent.all_operations'],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function note(InternalNote $note): array
    {
        return [
            'at' => $note->created_at,
            'kind' => 'profile::profile.recent.kinds.message',
            'text' => Str::limit((string) $note->free_text, self::TEXT_LIMIT),
            'vessel' => $note->vessel?->name,
            'url' => null,
        ];
    }

    /** @return array<string, mixed> */
    private static function alert(Alert $alert, bool $canTrack): array
    {
        return [
            'at' => $alert->triggered_at,
            'kind' => 'profile::profile.recent.kinds.alert',
            'text' => __($alert->type->label()),
            'vessel' => $alert->vessel?->name,
            'url' => $canTrack ? route('tracking.alerts.show', $alert) : null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private static function latest(Collection $items): Collection
    {
        return $items->filter(fn (array $item) => $item['at'] !== null)->sortByDesc('at')->take(self::LIMIT)->values();
    }

    /**
     * @param  array<int, array{0: Permission, 1: string, 2: array<string, mixed>, 3: string}>  $candidates
     * @return array<int, array{url: string, label: string}>
     */
    private static function links(User $user, array $candidates): array
    {
        return collect($candidates)
            ->filter(fn (array $link) => $user->can($link[0]->value) && app('router')->has($link[1]))
            ->map(fn (array $link) => ['url' => route($link[1], $link[2]), 'label' => $link[3]])
            ->values()
            ->all();
    }
}
