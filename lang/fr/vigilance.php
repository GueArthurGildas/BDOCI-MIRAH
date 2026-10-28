<?php

return [
    'reasons' => [
        'open_infractions' => ':count infraction(s) en cours',
        'license_expired' => 'Licence expirée',
        'license_suspended' => 'Licence suspendue',
        'license_revoked' => 'Licence révoquée',
        'beacon_silent' => 'Balise silencieuse',
        'unexplained_variances' => ':count écart(s) de déclaration ZEE non expliqué(s)',
        'repeated_checks' => ':count vérifications sans suite',
        'vms_failure' => 'Avarie de balise en cours',
        'enhanced_monitoring' => 'Suivi renforcé',
        'license_expiring' => 'Licence expirant dans :days jour(s)',
    ],
    'score' => [
        'levels' => [
            'low' => 'Faible',
            'moderate' => 'Modérée',
            'high' => 'Élevée',
        ],
        'components' => [
            'silence_now' => 'Silence VMS en cours',
            'silences_12_months' => 'Silences VMS sur 12 mois',
            'license_blocked' => 'Licence expirée, suspendue ou révoquée',
            'license_expiring' => 'Licence proche de l\'échéance',
            'open_infractions' => 'Infractions en cours',
            'infractions_12_months' => 'Infractions sur 12 mois',
            'checks' => 'Vérifications répétées',
            'unexplained_variances' => 'Écarts ZEE non expliqués',
            'unanswered_messages' => 'Messages sans réponse',
            'no_inspection' => 'Navire vu mais non inspecté sur 12 mois',
        ],
    ],
];
