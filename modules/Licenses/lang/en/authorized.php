<?php

return [
    'title' => 'Authorized vessels',
    'subtitle' => 'List computed from active licenses (RG01)',
    'count' => 'Authorized vessels',
    'last_publication' => 'Last publication',
    'version' => 'Version :version',
    'never' => 'Never published',
    'freshness' => 'Freshness',
    'stale' => 'To publish again',
    'up_to_date' => 'Up to date',
    'list_title' => 'Current list',
    'publish' => 'Publish a new version',
    'history_title' => 'Publications',
    'version_column' => 'Version',
    'published_at' => 'Published at',
    'download' => 'Download',
    'published' => 'The list has been published.',
    'pdf' => [
        'title' => 'List of authorized vessels, version :version',
        'published_at' => 'Published on :date, :count vessels',
    ],
    'how_title' => 'How this list is calculated (rule RG01)',
    'how_rule' => 'A vessel is authorized when it holds a license with the "active" status whose expiry date has not passed.',
    'how_one_per_vessel' => 'A vessel appears only once: if several licenses meet the rule, the one expiring last is kept.',
    'how_excluded' => 'Suspended, revoked, expired or not yet active licenses do not count: the vessel leaves the list as soon as the status changes or the day after expiry.',
    'how_publication' => 'The current list is recalculated each time it is opened; the published version is a frozen copy, to republish when the "Freshness" indicator asks for it.',
];
