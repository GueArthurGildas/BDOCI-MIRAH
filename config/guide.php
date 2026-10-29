<?php

$list = [
    'toolbar' => '.gs-table-toolbar',
    'filters' => '.gs-filter-row',
    'table' => '.card .table',
];

return [
    'generic' => [
        'menu' => '.gs-sidebar',
        'tabs' => '.gs-tabs',
        'tools' => '.gs-tools',
    ],

    'pages' => [
        'dashboard' => ['stats' => '.gs-stat', 'zones' => '[data-guide="zones"]', 'infractions' => '[data-guide="infractions"]', 'sources' => '[data-guide="sources"]', 'observations' => '[data-guide="observations"]'],
        'fleet.index' => ['stats' => '.gs-stat', 'filter_card' => '[data-guide="filter_card"]', 'distribution' => '[data-guide="distribution"]', 'ranking' => '[data-guide="ranking"]'],
        'vessels.index' => ['exports' => '.gs-table-toolbar .btn-dark', ...$list, 'new' => '.card-header-right .btn-primary'],
        'vessels.show' => ['header' => '[data-guide="header"]', 'summary' => '[data-guide="summary"]', 'position' => '[data-guide="position"]', 'tabs' => '.tab-header'],
        'vessels.duplicates.index' => ['table' => '.card .table'],
        'watch.board.index' => ['stats' => '.gs-stat', 'relays' => '[data-guide="relays"]', 'checks' => '[data-guide="checks"]', 'watched' => '[data-guide="watched"]', 'instructions' => '[data-guide="instructions"]', 'observations' => '[data-guide="observations"]', 'add' => '[data-guide="add"]'],
        'watch.observations.index' => ['new' => '.card-header-right .btn-primary', 'table' => '.card .table'],
        'logbook.days.show' => ['entries' => '[data-guide="entries"]', 'new_entry' => '[data-guide="new_entry"]', 'close_day' => '[data-guide="close_day"]', 'pdf' => '[data-guide="pdf"]'],
        'tracking.map' => ['map' => '[data-guide="map"]'],
        'tracking.alerts.index' => ['stats' => '.gs-stat', 'table' => '.card .table'],
        'tracking.slots.index' => ['new' => 'form[action$="tracking/slots"]', 'table' => '.card .table'],
        'operations.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'operations.show' => ['details' => '[data-guide="details"]', 'events' => '[data-guide="events"]', 'add_event' => '[data-guide="add_event"]', 'instructions' => '[data-guide="instructions"]', 'close' => '[data-guide="close"]'],
        'licenses.index' => ['stats' => '.gs-stat', 'toolbar' => '.gs-table-toolbar', 'filters' => '.gs-filter-row', 'table' => '.card .table', 'new' => '.card-header-right .btn-primary'],
        'licenses.show' => ['identity' => '[data-guide="identity"]', 'scope' => '[data-guide="scope"]', 'documents' => '[data-guide="documents"]', 'suspension' => '[data-guide="suspension"]', 'revocation' => '[data-guide="revocation"]'],
        'infractions.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'infractions.show' => ['case' => '[data-guide="case"]', 'upheld' => '[data-guide="upheld"]', 'evidence' => '[data-guide="evidence"]', 'measures' => '[data-guide="measures"]', 'violation_report' => '[data-guide="violation_report"]', 'follow_ups' => '[data-guide="follow_ups"]', 'status' => '[data-guide="status"]'],
        'missions.index' => ['table' => '.card .table', 'new' => '.card-header-right .btn-primary'],
        'missions.show' => ['mission' => '[data-guide="mission"]', 'map' => '[data-guide="map"]', 'units' => '[data-guide="units"]', 'briefing' => '[data-guide="briefing"]', 'review' => '[data-guide="review"]', 'participants' => '[data-guide="participants"]'],
        'witnesses.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'communications.messages.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'movements.eez.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'movements.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'catches.index' => [...$list],
        'inspections.index' => [...$list],
        'administration.users.index' => [...$list, 'new' => '.card-header-right .btn-primary'],
        'administration.roles.index' => ['list' => '[data-guide="list"]', 'matrix' => '[data-guide="matrix"]', 'new' => '.card-header-right .btn-primary'],
        'administration.settings' => ['group' => '[data-guide="group"]'],
        'administration.connectors' => ['source' => '[data-guide="source"]'],
        'reports.exports.index' => ['type' => '[data-guide="type"]', 'log' => '[data-guide="log"]'],
    ],
];
