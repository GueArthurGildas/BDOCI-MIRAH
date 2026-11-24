<?php

namespace App\Support\Navigation;

use App\Support\Authorization\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Route;

final class MorningMenu
{
    private const SECTIONS = [
        'shift' => [
            'icon' => 'icon-sunrise',
            'links' => [
                ['watch.board.index', 'watch::watch.board.overview_nav', 'icon-sunrise', Permission::WatchView],
                ['logbook.handovers.create', 'ui.morning_menu.links.handover', 'icon-repeat', Permission::LogbookHandover],
                ['logbook.days.today', 'ui.morning_menu.links.today', 'icon-edit', Permission::LogbookView],
                ['watch.instructions.index', 'watch::watch.instructions.nav', 'icon-clipboard', Permission::WatchView],
            ],
        ],
        'watch' => [
            'icon' => 'icon-radio',
            'links' => [
                ['tracking.alerts.index', 'tracking::tracking.alerts.title', 'icon-alert-triangle', Permission::TrackingView],
                ['tracking.map', 'tracking::tracking.map.title', 'icon-map', Permission::TrackingView],
                ['tracking.slots.index', 'tracking::tracking.slots.title', 'icon-camera', Permission::TrackingView],
                ['movements.eez.index', 'movements::eez.title', 'icon-navigation', Permission::MovementsView],
                ['vms.failures.index', 'vms::vms.failures.title', 'icon-alert-octagon', Permission::VmsView],
                ['vms.beacons.index', 'vms::vms.beacons.title', 'icon-radio', Permission::VmsView],
            ],
        ],
        'record' => [
            'icon' => 'icon-book',
            'links' => [
                ['logbook.days.index', 'logbook::logbook.days.nav', 'icon-book', Permission::LogbookView],
                ['logbook.index', 'logbook::logbook.observations_nav', 'icon-eye', Permission::LogbookView],
                ['watch.observations.index', 'watch::watch.observations.nav', 'icon-search', Permission::WatchView],
                ['witnesses.index', 'witnesses::witnesses.title', 'icon-users', Permission::WitnessesView],
                ['vessels.index', 'vessels::vessels.title', 'icon-anchor', Permission::VesselsView],
            ],
        ],
        'operations' => [
            'icon' => 'icon-layers',
            'links' => [
                ['operations.index', 'operations::operations.title', 'icon-layers', Permission::OperationsView],
                ['operations.create', 'ui.morning_menu.links.new_operation', 'icon-plus-circle', Permission::OperationsCreate],
                ['communications.messages.index', 'communications::communications.title', 'icon-mail', Permission::CommunicationsView],
            ],
        ],
        'follow_up' => [
            'icon' => 'icon-award',
            'links' => [
                ['watch.rotations.index', 'watch::watch.rotations.nav', 'icon-users', Permission::WatchManage],
                ['infractions.index', 'infractions::infractions.title', 'icon-alert-triangle', Permission::InfractionsView],
                ['missions.index', 'missions::missions.title', 'icon-flag', Permission::MissionsView],
                ['witnesses.distributions.index', 'witnesses::distributions.title', 'icon-share', Permission::WitnessesManage],
            ],
        ],
    ];

    /** @return array<int, array{key: string, icon: string, links: array<int, array{url: string, label: string, icon: string, active: bool}>}> */
    public function sectionsFor(?Authorizable $user): array
    {
        if ($user === null) {
            return [];
        }

        return collect(self::SECTIONS)
            ->map(fn (array $section, string $key) => [
                'key' => $key,
                'icon' => $section['icon'],
                'links' => collect($section['links'])
                    ->filter(fn (array $link) => Route::has($link[0]) && $user->can($link[3]->value))
                    ->map(fn (array $link) => [
                        'url' => route($link[0]),
                        'label' => $link[1],
                        'icon' => $link[2],
                        'active' => request()->routeIs($link[0]),
                    ])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $section) => $section['links'] !== [])
            ->values()
            ->all();
    }
}
