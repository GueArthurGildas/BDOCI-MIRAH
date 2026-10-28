<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Schéma de données

Tables de l'application (hors tables du framework) et clés étrangères, relevées dans la base migrée.

```mermaid
erDiagram
    accreditations {
        int8 id PK
        int8 agent_id FK
        varchar card_number
        varchar decision
        date returned_on
        text trainings
        timestamp created_at
        timestamp updated_at
    }
    action_catalog {
        int8 id PK
        int8 module_id FK
        varchar code
        varchar verb
        bool ai_agent_allowed
        bool ai_agent_enabled
        timestamp created_at
        timestamp updated_at
    }
    activity_log {
        int8 id PK
        varchar log_name
        text description
        varchar subject_type
        int8 subject_id
        varchar event
        varchar causer_type
        int8 causer_id
        json attribute_changes
        json properties
        timestamp created_at
        timestamp updated_at
    }
    agents {
        int8 id PK
        int8 user_id FK
        varchar name
        varchar grade
        varchar agency
        bool is_inspector
        timestamp created_at
        timestamp updated_at
    }
    catch_declarations {
        int8 id PK
        int8 vessel_id FK
        int8 species_id FK
        int8 zone_id FK
        date period_start
        date period_end
        numeric quantity_kg
        varchar source
        timestamp created_at
        timestamp updated_at
    }
    comments {
        int8 id PK
        varchar object_type
        int8 object_id
        int8 user_id FK
        varchar kind
        text body
        timestamp created_at
    }
    contacts {
        int8 id PK
        varchar category
        int8 country_id FK
        int8 institution_id FK
        varchar organization
        varchar email
        varchar phone
        timestamp created_at
        timestamp updated_at
    }
    countries {
        int8 id PK
        bpchar iso3_code
        bpchar iso2_code
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    custom_field_definitions {
        int8 id PK
        varchar object_type
        varchar code
        varchar label
        varchar type
        bool is_visible
        timestamp created_at
        timestamp updated_at
    }
    custom_field_values {
        int8 id PK
        int8 custom_field_definition_id FK
        int8 object_id
        text value
        timestamp created_at
        timestamp updated_at
    }
    data_source_errors {
        int8 id PK
        int8 data_source_id FK
        int8 data_source_sync_id FK
        timestamp occurred_at
        varchar code
        text message
        timestamp resolved_at
        int8 resolved_by FK
        timestamp created_at
        timestamp updated_at
    }
    data_source_rejections {
        int8 id PK
        int8 data_source_id FK
        int8 data_source_sync_id FK
        varchar external_ref
        varchar reason
        text details
        jsonb payload
        varchar status
        timestamp received_at
        timestamp handled_at
        int8 handled_by FK
        timestamp created_at
        timestamp updated_at
    }
    data_source_syncs {
        int8 id PK
        int8 data_source_id FK
        timestamp started_at
        timestamp finished_at
        int4 received_count
        int4 rejected_count
        text message
        timestamp created_at
        timestamp updated_at
    }
    data_sources {
        int8 id PK
        varchar key
        varchar name
        varchar kind
        varchar status
        timestamp last_synced_at
        timestamp created_at
        timestamp updated_at
    }
    document_links {
        int8 document_id FK
        varchar object_type
        int8 object_id
    }
    documents {
        int8 id PK
        varchar name
        varchar mime_type
        varchar path
        bpchar sha256_fingerprint
        bool is_frozen
        int8 filed_by FK
        timestamp created_at
    }
    external_list_vessel {
        int8 vessel_id FK
        int8 external_list_id FK
        date listed_from
        date listed_until
    }
    external_lists {
        int8 id PK
        int8 institution_id FK
        varchar type
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    gears {
        int8 id PK
        varchar code_fao
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    infraction_types {
        int8 id PK
        varchar code
        varchar name
        varchar legal_basis
        timestamp created_at
        timestamp updated_at
    }
    infractions {
        int8 id PK
        varchar reference
        int8 vessel_id FK
        int8 inspection_id FK
        int8 infraction_type_id FK
        timestamp detected_at
        text facts
        text evidence
        varchar status
        text decision
        varchar sanction
        timestamp created_at
        timestamp updated_at
    }
    inspections {
        int8 id PK
        int8 vessel_id FK
        int8 port_id FK
        varchar external_reference
        varchar type
        timestamp inspected_at
        varchar inspectors
        varchar result
        text findings
        timestamp created_at
        timestamp updated_at
    }
    institutions {
        int8 id PK
        varchar type
        varchar name
        int8 country_id FK
        timestamp created_at
        timestamp updated_at
    }
    legal_entities {
        int8 id PK
        varchar name
        varchar address
        varchar phone
        varchar email
        int8 country_id FK
        timestamp created_at
        timestamp updated_at
    }
    licenses {
        int8 id PK
        int8 vessel_id FK
        varchar number
        varchar type
        date valid_from
        date valid_until
        timestamp suspended_at
        timestamp created_at
        timestamp updated_at
    }
    mission_user {
        int8 mission_id FK
        int8 user_id FK
    }
    mission_vessel {
        int8 mission_id FK
        int8 vessel_id FK
    }
    missions {
        int8 id PK
        varchar reference
        int8 zone_id FK
        varchar type
        text objective
        timestamp starts_at
        timestamp ends_at
        varchar status
        text results
        timestamp created_at
        timestamp updated_at
    }
    module_registry {
        int8 id PK
        varchar code
        varchar label
        varchar version
        bool is_active
        jsonb dependencies
        timestamp created_at
        timestamp updated_at
    }
    movements {
        int8 id PK
        int8 vessel_id FK
        int8 port_id FK
        int8 recorded_by FK
        varchar direction
        timestamp occurred_at
        varchar reason
        text notes
        timestamp created_at
        timestamp updated_at
    }
    notifications {
        uuid id PK
        varchar type
        varchar notifiable_type
        int8 notifiable_id
        jsonb data
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    observations {
        int8 id PK
        int8 vessel_id FK
        int8 team_id FK
        int8 author_id FK
        int8 zone_id FK
        timestamp observed_at
        numeric latitude
        numeric longitude
        text description
        varchar level
        varchar status
        text follow_up
        timestamp closed_at
        timestamp created_at
        timestamp updated_at
    }
    people {
        int8 id PK
        varchar name
        varchar first_name
        int8 nationality_country_id FK
        varchar address
        varchar phone
        varchar email
        int8 legal_entity_id FK
        timestamp created_at
        timestamp updated_at
    }
    permission_grants {
        int8 id PK
        int8 role_id FK
        int8 permission_group_id FK
        int8 action_id FK
        int8 zone_id FK
        date valid_from
        date valid_to
        timestamp created_at
        timestamp updated_at
    }
    permission_group_user {
        int8 user_id FK
        int8 permission_group_id FK
    }
    permission_groups {
        int8 id PK
        varchar code
        varchar label
        bool is_system
        timestamp created_at
        timestamp updated_at
    }
    ports {
        int8 id PK
        varchar name
        varchar locality
        numeric latitude
        numeric longitude
        int8 zone_id FK
        timestamp created_at
        timestamp updated_at
        varchar code
        bool is_designated
    }
    positions {
        int8 id PK
        int8 vessel_id FK
        int8 tracking_device_id FK
        int8 zone_id FK
        varchar system
        timestamp recorded_at
        numeric latitude
        numeric longitude
        numeric speed_knots
        int2 course
    }
    presentations {
        int8 id PK
        varchar code
        varchar label
        numeric conversion_factor
        timestamp created_at
        timestamp updated_at
    }
    reference_list_values {
        int8 id PK
        int8 reference_list_id FK
        varchar code
        varchar label
        int4 sort_order
        bool is_active
        timestamp created_at
        timestamp updated_at
    }
    reference_lists {
        int8 id PK
        varchar code
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    role_user {
        int8 user_id FK
        int8 role_id FK
    }
    roles {
        int8 id PK
        varchar name
        timestamp created_at
        timestamp updated_at
        text description
        int8 parent_id FK
    }
    settings {
        int8 id PK
        varchar key
        varchar value
        int8 updated_by FK
        timestamp created_at
        timestamp updated_at
    }
    source_priorities {
        varchar field
        varchar source
        int2 rank
    }
    species {
        int8 id PK
        varchar fao_code
        varchar name
        varchar scientific_name
        timestamp created_at
        timestamp updated_at
    }
    teams {
        int8 id PK
        varchar name
        timestamp created_at
        timestamp updated_at
        int8 lead_user_id FK
    }
    tracking_devices {
        int8 id PK
        int8 vessel_id FK
        varchar system
        varchar identifier
        date installed_at
        date removed_at
        timestamp created_at
        timestamp updated_at
    }
    users {
        int8 id PK
        varchar name
        varchar email
        timestamp email_verified_at
        varchar password
        varchar remember_token
        timestamp created_at
        timestamp updated_at
        int8 team_id FK
        bool is_active
        varchar type
        varchar job_title
        varchar phone
        varchar whatsapp_number
        timestamp last_login_at
    }
    vessel_actors {
        int8 id PK
        int8 vessel_id FK
        varchar role
        int8 legal_entity_id FK
        int8 person_id FK
        date started_on
        date ended_on
        timestamp created_at
        timestamp updated_at
    }
    vessel_current_states {
        int8 vessel_id FK
        varchar license_status
        date license_expires_on
        bool vms_failure_ongoing
        timestamp last_position_at
        numeric vms_silence_hours
        bool is_enhanced_monitoring
        int4 open_infractions_count
        int4 checks_count
        varchar status_light
        timestamp updated_at
    }
    vessel_events {
        int8 id PK
        int8 vessel_id FK
        timestamp occurred_at
        varchar type
        int8 module_id FK
        varchar object_type
        int8 object_id
        varchar summary_key
        jsonb summary_params
        int8 zone_id FK
        int8 user_id FK
        varchar source
        timestamp created_at
        timestamp updated_at
    }
    vessel_field_provenance {
        int8 vessel_id FK
        varchar field
        varchar source
        timestamp updated_at
    }
    vessel_history {
        int8 id PK
        int8 vessel_id FK
        varchar former_name
        int8 former_flag_country_id FK
        date changed_on
        timestamp created_at
        timestamp updated_at
    }
    vessel_identifiers {
        int8 id PK
        int8 vessel_id FK
        varchar type
        varchar value
        date valid_from
        date valid_to
        timestamp created_at
        timestamp updated_at
    }
    vessel_sources {
        int8 id PK
        int8 vessel_id FK
        varchar source
        varchar external_id
        timestamp last_import_at
        jsonb raw_data
        varchar matching_status
        int8 candidate_vessel_id FK
        varchar matching_criterion
        int8 settled_by FK
        timestamp settled_at
        timestamp created_at
        timestamp updated_at
    }
    vessel_types {
        int8 id PK
        varchar code
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    vessels {
        int8 id PK
        varchar name
        varchar registration_number
        varchar imo_number
        varchar call_sign
        varchar type
        varchar category
        numeric length_m
        numeric gross_tonnage
        varchar status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
        varchar record_status
        varchar creation_origin
        varchar fleet_segment
        int8 vessel_type_id FK
        varchar fleet_type
        bool is_tuna_vessel
        varchar legal_status
        int8 flag_country_id FK
        int8 home_port_id FK
        int2 construction_year
        numeric beam_m
        numeric depth_over_keel_m
        numeric net_tonnage
        varchar propulsion
        numeric hold_capacity_m3
        varchar registration_certificate
        varchar fao_identifier
        bool license_not_provided
    }
    zone_rules {
        int8 id PK
        int8 zone_id FK
        varchar segment
        int2 nautical_miles
        timestamp created_at
        timestamp updated_at
    }
    zones {
        int8 id PK
        varchar name
        varchar type
        json geometry
        bool is_active
        timestamp created_at
        timestamp updated_at
    }
    action_catalog ||--o{ permission_grants : "action_id"
    agents ||--o{ accreditations : "agent_id"
    countries ||--o{ contacts : "country_id"
    countries ||--o{ institutions : "country_id"
    countries ||--o{ legal_entities : "country_id"
    countries ||--o{ people : "nationality_country_id"
    countries ||--o{ vessel_history : "former_flag_country_id"
    countries ||--o{ vessels : "flag_country_id"
    custom_field_definitions ||--o{ custom_field_values : "custom_field_definition_id"
    data_source_syncs ||--o{ data_source_errors : "data_source_sync_id"
    data_source_syncs ||--o{ data_source_rejections : "data_source_sync_id"
    data_sources ||--o{ data_source_errors : "data_source_id"
    data_sources ||--o{ data_source_rejections : "data_source_id"
    data_sources ||--o{ data_source_syncs : "data_source_id"
    documents ||--o{ document_links : "document_id"
    external_lists ||--o{ external_list_vessel : "external_list_id"
    infraction_types ||--o{ infractions : "infraction_type_id"
    inspections ||--o{ infractions : "inspection_id"
    institutions ||--o{ contacts : "institution_id"
    institutions ||--o{ external_lists : "institution_id"
    legal_entities ||--o{ people : "legal_entity_id"
    legal_entities ||--o{ vessel_actors : "legal_entity_id"
    missions ||--o{ mission_user : "mission_id"
    missions ||--o{ mission_vessel : "mission_id"
    module_registry ||--o{ action_catalog : "module_id"
    module_registry ||--o{ vessel_events : "module_id"
    people ||--o{ vessel_actors : "person_id"
    permission_groups ||--o{ permission_grants : "permission_group_id"
    permission_groups ||--o{ permission_group_user : "permission_group_id"
    ports ||--o{ inspections : "port_id"
    ports ||--o{ movements : "port_id"
    ports ||--o{ vessels : "home_port_id"
    reference_lists ||--o{ reference_list_values : "reference_list_id"
    roles ||--o{ permission_grants : "role_id"
    roles ||--o{ role_user : "role_id"
    roles ||--o{ roles : "parent_id"
    species ||--o{ catch_declarations : "species_id"
    teams ||--o{ observations : "team_id"
    teams ||--o{ users : "team_id"
    tracking_devices ||--o{ positions : "tracking_device_id"
    users ||--o{ agents : "user_id"
    users ||--o{ comments : "user_id"
    users ||--o{ data_source_errors : "resolved_by"
    users ||--o{ data_source_rejections : "handled_by"
    users ||--o{ documents : "filed_by"
    users ||--o{ mission_user : "user_id"
    users ||--o{ movements : "recorded_by"
    users ||--o{ observations : "author_id"
    users ||--o{ permission_group_user : "user_id"
    users ||--o{ role_user : "user_id"
    users ||--o{ settings : "updated_by"
    users ||--o{ teams : "lead_user_id"
    users ||--o{ vessel_events : "user_id"
    users ||--o{ vessel_sources : "settled_by"
    vessel_types ||--o{ vessels : "vessel_type_id"
    vessels ||--o{ catch_declarations : "vessel_id"
    vessels ||--o{ external_list_vessel : "vessel_id"
    vessels ||--o{ infractions : "vessel_id"
    vessels ||--o{ inspections : "vessel_id"
    vessels ||--o{ licenses : "vessel_id"
    vessels ||--o{ mission_vessel : "vessel_id"
    vessels ||--o{ movements : "vessel_id"
    vessels ||--o{ observations : "vessel_id"
    vessels ||--o{ positions : "vessel_id"
    vessels ||--o{ tracking_devices : "vessel_id"
    vessels ||--o{ vessel_actors : "vessel_id"
    vessels ||--o{ vessel_current_states : "vessel_id"
    vessels ||--o{ vessel_events : "vessel_id"
    vessels ||--o{ vessel_field_provenance : "vessel_id"
    vessels ||--o{ vessel_history : "vessel_id"
    vessels ||--o{ vessel_identifiers : "vessel_id"
    vessels ||--o{ vessel_sources : "candidate_vessel_id"
    vessels ||--o{ vessel_sources : "vessel_id"
    zones ||--o{ catch_declarations : "zone_id"
    zones ||--o{ missions : "zone_id"
    zones ||--o{ observations : "zone_id"
    zones ||--o{ permission_grants : "zone_id"
    zones ||--o{ ports : "zone_id"
    zones ||--o{ positions : "zone_id"
    zones ||--o{ vessel_events : "zone_id"
    zones ||--o{ zone_rules : "zone_id"
```
