<?php

return [
    'recorded' => 'License :number recorded, valid until :expires_on.',
    'updated' => 'License :number updated, valid until :expires_on.',
    'document_attached' => 'Document :name attached to license :number.',
    'suspended' => [
        'force_majeure' => 'License :number suspended from :started_on (force majeure).',
        'stocks' => 'License :number suspended from :started_on (stocks).',
        'art_105' => 'License :number suspended from :started_on (article 105).',
        'art_106' => 'License :number suspended from :started_on (article 106).',
        'vms_overdue' => 'License :number suspended from :started_on (VMS repair overdue).',
        'other' => 'License :number suspended from :started_on (other reason).',
    ],
    'suspension_lifted' => 'Suspension of license :number lifted on :lifted_on.',
    'revoked' => 'License :number revoked on :revoked_on.',
    'fnp' => [
        'registered' => 'Registered in the FNP since :status_on.',
        'deregistered' => 'Deregistered from the FNP on :status_on.',
    ],
    'vms_certificate_recorded' => 'VMS compatibility certificate recorded, valid until :expires_on.',
    'external_authorization_recorded' => 'Authorization from :institution recorded, valid until :valid_to.',
];
