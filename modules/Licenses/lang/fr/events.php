<?php

return [
    'recorded' => 'Licence :number enregistrée, valable jusqu\'au :expires_on.',
    'updated' => 'Licence :number modifiée, valable jusqu\'au :expires_on.',
    'document_attached' => 'Document :name rattaché à la licence :number.',
    'suspended' => [
        'force_majeure' => 'Licence :number suspendue à partir du :started_on (force majeure).',
        'stocks' => 'Licence :number suspendue à partir du :started_on (stocks).',
        'art_105' => 'Licence :number suspendue à partir du :started_on (article 105).',
        'art_106' => 'Licence :number suspendue à partir du :started_on (article 106).',
        'vms_overdue' => 'Licence :number suspendue à partir du :started_on (réparation VMS hors délai).',
        'other' => 'Licence :number suspendue à partir du :started_on (autre motif).',
    ],
    'suspension_lifted' => 'Suspension de la licence :number levée le :lifted_on.',
    'revoked' => 'Licence :number révoquée le :revoked_on.',
    'fnp' => [
        'registered' => 'Inscrit au FNP depuis le :status_on.',
        'deregistered' => 'Radié du FNP le :status_on.',
    ],
    'vms_certificate_recorded' => 'Certificat de compatibilité VMS enregistré, valable jusqu\'au :expires_on.',
    'external_authorization_recorded' => 'Autorisation de :institution enregistrée, valable jusqu\'au :valid_to.',
];
