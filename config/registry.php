<?php

return [
    'default_source_order' => ['manual_entry', 'themis', 'neno', 'e_inspection', 'dpep', 'mrep'],

    'tracked_vessel_fields' => [
        'name', 'flag_country_id', 'vessel_type_id', 'fleet_type', 'fleet_segment', 'legal_status', 'is_tuna_vessel',
        'home_port_id', 'construction_year', 'length_m', 'beam_m', 'depth_over_keel_m', 'gross_tonnage', 'net_tonnage',
        'propulsion', 'hold_capacity_m3', 'registration_certificate',
    ],
];
