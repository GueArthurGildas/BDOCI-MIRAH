<?php

return [
    'map' => [
        'title' => 'Carte de situation',
        'category' => 'Catégorie de navire',
        'subtitle' => 'Dernière position connue de chaque navire et zones de surveillance',
        'positions_count' => '{0} Aucun navire positionné|{1} :count navire positionné|[2,*] :count navires positionnés',
        'legend' => [
            'emitting' => 'Balise émettrice',
            'silent' => 'Rupture d\'émission',
            'patrol' => 'Vedette',
        ],
    ],
    'zones' => [
        'title' => 'Situation par zone',
        'subtitle' => 'Nombre de navires présents dans chaque zone à un instant donné',
        'situation_at' => 'Situation à :time',
        'at' => 'Date et heure',
        'chart_title' => 'Navires en zones portuaires',
        'table_title' => 'Toutes les zones',
        'freshness_hint' => 'Seules les positions reçues dans les :hours heures précédentes sont comptées.',
        'zone' => 'Zone',
        'type' => 'Type',
        'vessels' => 'Navires',
    ],
    'beacons' => [
        'title' => 'État des balises',
        'subtitle' => 'Émissions VMS et AIS des balises installées',
        'list_title' => 'Balises installées',
        'threshold_hint' => 'Une balise est en rupture d\'émission après :hours heures sans émission.',
        'vessel' => 'Navire',
        'system' => 'Système',
        'identifier' => 'Identifiant',
        'last_emission' => 'Dernière émission',
        'zone' => 'Zone',
        'status' => 'Statut',
        'never' => 'Jamais',
    ],
];
