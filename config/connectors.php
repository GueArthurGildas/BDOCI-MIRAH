<?php

use App\Support\Connectors\Sources\EInspectionConnector;
use App\Support\Connectors\Sources\MrepConnector;
use App\Support\Connectors\Sources\NenoConnector;
use App\Support\Connectors\Sources\ThemisConnector;

return [
    'fixtures_path' => env('CONNECTORS_FIXTURES_PATH', database_path('fixtures/connectors')),
    'import_directory' => 'connectors/imports',
    'sync_actor_role' => 'admin',
    'sources' => [
        EInspectionConnector::class,
        MrepConnector::class,
        ThemisConnector::class,
        NenoConnector::class,
    ],
];
