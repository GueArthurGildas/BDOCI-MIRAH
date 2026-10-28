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
    ai_agent_actions {
        int8 id PK
        int8 action_id FK
        int8 requested_by FK
        text instruction
        jsonb parameters
        varchar status
        timestamp executed_at
        varchar result_object_type
        int8 result_object_id
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
    check_counters {
        int8 vessel_id FK
        int4 checks_count
        timestamp last_checked_at
        bool is_alert_raised
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
    communication_channels {
        int8 id PK
        varchar code
        bool is_auto_dispatch_enabled
        bool is_consent_required
        timestamp created_at
        timestamp updated_at
    }
    consistency_rules {
        int8 id PK
        varchar code
        varchar description
        jsonb parameters
        bool is_active
        timestamp created_at
        timestamp updated_at
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
        varchar code
        varchar name
        varchar mode
        varchar indicator_status
        timestamp last_synced_at
        timestamp created_at
        timestamp updated_at
        text last_error
        bool is_stale
    }
    declared_observed_catches {
        int8 id PK
        int8 inspection_id FK
        int8 species_id FK
        int8 presentation_id FK
        numeric declared_weight_kg
        numeric observed_weight_kg
        numeric net_weight_kg
        numeric conversion_factor
        numeric variance_pct
        bool exceeds_csp_threshold
        timestamp created_at
        timestamp updated_at
    }
    dispatch_rules {
        int8 id PK
        int8 message_template_id FK
        varchar trigger_event
        jsonb conditions
        int4 frequency_minutes
        int4 daily_cap
        varchar stop_condition
        varchar mode
        bool is_active
        int8 editable_by_permission_group_id FK
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
    eez_declaration_catches {
        int8 id PK
        int8 eez_declaration_id FK
        int8 species_id FK
        numeric weight_kg
        timestamp created_at
        timestamp updated_at
    }
    eez_declaration_checks {
        int8 id PK
        int8 eez_declaration_id FK
        varchar check_type
        varchar result
        varchar comment
        timestamp created_at
        timestamp updated_at
    }
    eez_declarations {
        int8 id PK
        int8 vessel_id FK
        varchar type
        timestamp received_at
        int8 reception_channel_id FK
        varchar entry_origin
        int8 entered_by FK
        timestamp crossed_at
        numeric latitude
        numeric longitude
        varchar route
        numeric speed
        varchar destination
        varchar reason
        varchar declared_name
        varchar declared_imo
        varchar declared_mmsi
        varchar declared_ircs
        varchar declared_flag
        varchar declared_owner
        varchar status
        timestamp created_at
        timestamp updated_at
    }
    email_connectors {
        int8 id PK
        varchar supplier
        varchar domain
        jsonb parameters
        bool is_active
        timestamp created_at
        timestamp updated_at
    }
    entry_exit_alerts {
        int8 id PK
        int8 vessel_id FK
        int8 eez_declaration_id FK
        varchar direction
        timestamp alerted_at
        varchar status
        timestamp created_at
        timestamp updated_at
    }
    entry_exit_links {
        int8 id PK
        int8 entry_declaration_id FK
        int8 exit_declaration_id FK
        numeric variance_kg
        varchar explanation
        timestamp created_at
        timestamp updated_at
    }
    export_log {
        int8 id PK
        int8 user_id FK
        varchar subject
        varchar permission
        varchar format
        jsonb filters
        int4 row_count
        timestamp exported_at
        timestamp created_at
        timestamp updated_at
    }
    external_inspections {
        int8 id PK
        varchar external_id
        varchar reference
        int8 vessel_id FK
        varchar location_type
        int8 port_id FK
        numeric latitude
        numeric longitude
        timestamp started_at
        timestamp ended_at
        varchar control_type
        varchar result
        varchar captain_signature_status
        bool is_copy_handed_over
        jsonb raw
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    external_links {
        int8 id PK
        varchar source
        varchar model_url
        bool is_copy_fallback
        timestamp created_at
        timestamp updated_at
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
    inspection_documents {
        int8 id PK
        int8 inspection_id FK
        varchar type
        bool is_compliant
        timestamp created_at
        timestamp updated_at
    }
    inspection_gears {
        int8 id PK
        int8 inspection_id FK
        int8 gear_id FK
        numeric mesh_mm
        bool is_marking_compliant
        bool is_compliant
        timestamp created_at
        timestamp updated_at
    }
    inspection_infractions {
        int8 id PK
        int8 inspection_id FK
        varchar provision
        varchar stamp
        text measures
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
    internal_notes {
        int8 id PK
        varchar type
        int8 vessel_id FK
        varchar urgency
        int8 recipient_user_id FK
        int8 recipient_team_id FK
        timestamp due_date
        text free_text
        int8 author_id FK
        int8 ai_agent_action_id FK
        bool is_read
        timestamp read_at
        varchar relayed_by
        int8 logbook_entry_id FK
        timestamp created_at
    }
    landings {
        int8 id PK
        int8 vessel_id FK
        int8 port_id FK
        timestamp landed_at
        varchar origin
        jsonb lines
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
    logbook_closings {
        int8 logbook_day_id FK
        jsonb counters
        int8 closed_by FK
        timestamp closed_at
    }
    logbook_days {
        int8 id PK
        date day
        timestamp shift_start_at
        timestamp shift_end_at
        varchar status
        int8 pdf_document_id FK
        timestamp locked_at
        timestamp created_at
        timestamp updated_at
    }
    logbook_entries {
        int8 id PK
        int8 logbook_day_id FK
        timestamp occurred_at
        int8 channel_id FK
        text content
        varchar direction
        bool is_order_or_decision
        int8 operation_id FK
        varchar handling_status
        int8 corrects_entry_id FK
        int8 entered_by FK
        timestamp created_at
        timestamp updated_at
    }
    logbook_entry_vessel {
        int8 logbook_entry_id FK
        int8 vessel_id FK
    }
    message_templates {
        int8 id PK
        varchar code
        varchar channel
        varchar language
        varchar subject
        text body
        varchar form
        bool is_active
        timestamp created_at
        timestamp updated_at
    }
    missed_declarations {
        int8 id PK
        int8 vessel_id FK
        timestamp detected_at
        int4 reminders
        varchar qualification
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
    mrep_dpeps {
        int8 id PK
        varchar external_id
        int8 vessel_id FK
        varchar number
        int8 port_id FK
        timestamp planned_arrival_at
        varchar purpose
        varchar last_port_call
        jsonb catches_on_board
        jsonb catches_to_land
        bool is_complete
        bool is_72h_deadline_met
        jsonb raw
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }
    mrep_entry_decisions {
        int8 id PK
        int8 dpep_id FK
        varchar decision
        timestamp decided_at
        varchar decision_maker
        timestamp created_at
        timestamp updated_at
    }
    mrep_gies_refusals {
        int8 id PK
        int8 vessel_id FK
        varchar reference
        date refused_on
        timestamp created_at
        timestamp updated_at
    }
    mrep_notifications {
        int8 id PK
        int8 dpep_id FK
        varchar recipient
        varchar channel
        timestamp notified_at
        varchar status
        timestamp created_at
        timestamp updated_at
    }
    mrep_port_calls {
        int8 id PK
        int8 dpep_id FK
        timestamp arrival
        timestamp departure
        bool is_inspected
        bool is_infraction_recorded
        int8 inspection_id FK
        timestamp created_at
        timestamp updated_at
    }
    mrep_targetings {
        int8 id PK
        int2 year
        numeric objective_pct
        numeric achieved_pct
        varchar target
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
    operation_archives {
        int8 operation_id FK
        timestamp archived_at
    }
    operation_categories {
        int8 id PK
        varchar code
        varchar label
        timestamp created_at
        timestamp updated_at
    }
    operation_contexts {
        int8 operation_id FK
        text first_event
        numeric temperature
        numeric pressure
        varchar visibility
        varchar cloud_cover
        int2 wind_beaufort
        varchar sea_state
    }
    operation_counters {
        int8 category_id FK
        int2 year
        int4 last_number
    }
    operation_events {
        int8 id PK
        int8 operation_id FK
        timestamp occurred_at
        text body
        timestamp created_at
        timestamp updated_at
    }
    operation_instructions {
        int8 id PK
        int8 operation_id FK
        text body
        int8 given_by FK
        timestamp created_at
        timestamp updated_at
    }
    operation_user {
        int8 operation_id FK
        int8 user_id FK
        varchar role
    }
    operation_vessel {
        int8 operation_id FK
        int8 vessel_id FK
    }
    operations {
        int8 id PK
        varchar code
        int8 category_id FK
        int4 number
        int2 year
        varchar name
        varchar status
        timestamp started_at
        timestamp ended_at
        varchar origin
        text summary
        text result
        int8 created_by FK
        int8 closed_by FK
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
    relays {
        int8 id PK
        int8 shift_handover_id FK
        int8 surveillance_observation_id FK
        int8 next_team_id FK
        int8 read_acknowledged_by FK
        timestamp acknowledged_at
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
    rotations {
        int8 id PK
        int8 team_id FK
        date day
        time start_time
        time end_time
        timestamp created_at
        timestamp updated_at
    }
    scheduled_reports {
        int8 id PK
        varchar code
        varchar frequency
        varchar format
        int8 recipient_id FK
        bool is_active
        timestamp created_at
        timestamp updated_at
    }
    sent_messages {
        int8 id PK
        int8 message_template_id FK
        int8 dispatch_rule_id FK
        int8 communication_channel_id FK
        int8 vessel_id FK
        int8 operation_id FK
        int8 logbook_entry_id FK
        varchar recipient
        varchar subject
        text body
        varchar status
        timestamp sent_at
        timestamp acknowledged_at
        varchar triggered_by
        int8 user_id FK
        bool reply_received
        timestamp created_at
        timestamp updated_at
    }
    settings {
        int8 id PK
        varchar key
        varchar value
        int8 updated_by FK
        timestamp created_at
        timestamp updated_at
    }
    shift_handovers {
        int8 id PK
        int8 rotation_id FK
        timestamp started_at
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
    standing_instructions {
        int8 id PK
        int8 vessel_id FK
        text body
        date started_on
        date ended_on
        timestamp created_at
        timestamp updated_at
    }
    surveillance_observations {
        int8 id PK
        int8 vessel_id FK
        int8 slot_position_id
        int8 logbook_entry_id FK
        text body
        varchar status
        int8 created_by FK
        timestamp created_at
        int8 legacy_observation_id FK
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
    transshipments {
        int8 id PK
        int8 vessel_id FK
        int8 partner_vessel_id FK
        timestamp transshipped_at
        varchar origin
        jsonb lines
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
    watch_entries {
        int8 id PK
        int8 vessel_id FK
        varchar origin
        timestamp started_at
        varchar status
        timestamp closed_at
        int8 logbook_entry_id FK
        int8 operation_id FK
        int8 entry_exit_alert_id FK
        int8 decided_by FK
        timestamp created_at
        timestamp updated_at
    }
    zone_counts {
        int8 control_slot_id
        int8 zone_id FK
        int4 vessels_count
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
    action_catalog ||--o{ ai_agent_actions : "action_id"
    action_catalog ||--o{ permission_grants : "action_id"
    agents ||--o{ accreditations : "agent_id"
    ai_agent_actions ||--o{ internal_notes : "ai_agent_action_id"
    communication_channels ||--o{ sent_messages : "communication_channel_id"
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
    dispatch_rules ||--o{ sent_messages : "dispatch_rule_id"
    documents ||--o{ document_links : "document_id"
    documents ||--o{ logbook_days : "pdf_document_id"
    eez_declarations ||--o{ eez_declaration_catches : "eez_declaration_id"
    eez_declarations ||--o{ eez_declaration_checks : "eez_declaration_id"
    eez_declarations ||--o{ entry_exit_alerts : "eez_declaration_id"
    eez_declarations ||--o{ entry_exit_links : "entry_declaration_id"
    eez_declarations ||--o{ entry_exit_links : "exit_declaration_id"
    entry_exit_alerts ||--o{ watch_entries : "entry_exit_alert_id"
    external_inspections ||--o{ declared_observed_catches : "inspection_id"
    external_inspections ||--o{ inspection_documents : "inspection_id"
    external_inspections ||--o{ inspection_gears : "inspection_id"
    external_inspections ||--o{ inspection_infractions : "inspection_id"
    external_inspections ||--o{ mrep_port_calls : "inspection_id"
    external_lists ||--o{ external_list_vessel : "external_list_id"
    gears ||--o{ inspection_gears : "gear_id"
    infraction_types ||--o{ infractions : "infraction_type_id"
    inspections ||--o{ infractions : "inspection_id"
    institutions ||--o{ contacts : "institution_id"
    institutions ||--o{ external_lists : "institution_id"
    legal_entities ||--o{ people : "legal_entity_id"
    legal_entities ||--o{ vessel_actors : "legal_entity_id"
    logbook_days ||--o{ logbook_closings : "logbook_day_id"
    logbook_days ||--o{ logbook_entries : "logbook_day_id"
    logbook_entries ||--o{ internal_notes : "logbook_entry_id"
    logbook_entries ||--o{ logbook_entries : "corrects_entry_id"
    logbook_entries ||--o{ logbook_entry_vessel : "logbook_entry_id"
    logbook_entries ||--o{ sent_messages : "logbook_entry_id"
    logbook_entries ||--o{ surveillance_observations : "logbook_entry_id"
    logbook_entries ||--o{ watch_entries : "logbook_entry_id"
    message_templates ||--o{ dispatch_rules : "message_template_id"
    message_templates ||--o{ sent_messages : "message_template_id"
    missions ||--o{ mission_user : "mission_id"
    missions ||--o{ mission_vessel : "mission_id"
    module_registry ||--o{ action_catalog : "module_id"
    module_registry ||--o{ vessel_events : "module_id"
    mrep_dpeps ||--o{ mrep_entry_decisions : "dpep_id"
    mrep_dpeps ||--o{ mrep_notifications : "dpep_id"
    mrep_dpeps ||--o{ mrep_port_calls : "dpep_id"
    observations ||--o{ surveillance_observations : "legacy_observation_id"
    operation_categories ||--o{ operation_counters : "category_id"
    operation_categories ||--o{ operations : "category_id"
    operations ||--o{ logbook_entries : "operation_id"
    operations ||--o{ operation_archives : "operation_id"
    operations ||--o{ operation_contexts : "operation_id"
    operations ||--o{ operation_events : "operation_id"
    operations ||--o{ operation_instructions : "operation_id"
    operations ||--o{ operation_user : "operation_id"
    operations ||--o{ operation_vessel : "operation_id"
    operations ||--o{ sent_messages : "operation_id"
    operations ||--o{ watch_entries : "operation_id"
    people ||--o{ vessel_actors : "person_id"
    permission_groups ||--o{ dispatch_rules : "editable_by_permission_group_id"
    permission_groups ||--o{ permission_grants : "permission_group_id"
    permission_groups ||--o{ permission_group_user : "permission_group_id"
    ports ||--o{ external_inspections : "port_id"
    ports ||--o{ inspections : "port_id"
    ports ||--o{ landings : "port_id"
    ports ||--o{ movements : "port_id"
    ports ||--o{ mrep_dpeps : "port_id"
    ports ||--o{ vessels : "home_port_id"
    presentations ||--o{ declared_observed_catches : "presentation_id"
    reference_list_values ||--o{ eez_declarations : "reception_channel_id"
    reference_list_values ||--o{ logbook_entries : "channel_id"
    reference_lists ||--o{ reference_list_values : "reference_list_id"
    roles ||--o{ permission_grants : "role_id"
    roles ||--o{ role_user : "role_id"
    roles ||--o{ roles : "parent_id"
    rotations ||--o{ shift_handovers : "rotation_id"
    shift_handovers ||--o{ relays : "shift_handover_id"
    species ||--o{ catch_declarations : "species_id"
    species ||--o{ declared_observed_catches : "species_id"
    species ||--o{ eez_declaration_catches : "species_id"
    surveillance_observations ||--o{ relays : "surveillance_observation_id"
    teams ||--o{ internal_notes : "recipient_team_id"
    teams ||--o{ observations : "team_id"
    teams ||--o{ relays : "next_team_id"
    teams ||--o{ rotations : "team_id"
    teams ||--o{ users : "team_id"
    tracking_devices ||--o{ positions : "tracking_device_id"
    users ||--o{ agents : "user_id"
    users ||--o{ ai_agent_actions : "requested_by"
    users ||--o{ comments : "user_id"
    users ||--o{ data_source_errors : "resolved_by"
    users ||--o{ data_source_rejections : "handled_by"
    users ||--o{ documents : "filed_by"
    users ||--o{ eez_declarations : "entered_by"
    users ||--o{ export_log : "user_id"
    users ||--o{ internal_notes : "author_id"
    users ||--o{ internal_notes : "recipient_user_id"
    users ||--o{ logbook_closings : "closed_by"
    users ||--o{ logbook_entries : "entered_by"
    users ||--o{ mission_user : "user_id"
    users ||--o{ movements : "recorded_by"
    users ||--o{ observations : "author_id"
    users ||--o{ operation_instructions : "given_by"
    users ||--o{ operation_user : "user_id"
    users ||--o{ operations : "closed_by"
    users ||--o{ operations : "created_by"
    users ||--o{ permission_group_user : "user_id"
    users ||--o{ relays : "read_acknowledged_by"
    users ||--o{ role_user : "user_id"
    users ||--o{ scheduled_reports : "recipient_id"
    users ||--o{ sent_messages : "user_id"
    users ||--o{ settings : "updated_by"
    users ||--o{ surveillance_observations : "created_by"
    users ||--o{ teams : "lead_user_id"
    users ||--o{ vessel_events : "user_id"
    users ||--o{ vessel_sources : "settled_by"
    users ||--o{ watch_entries : "decided_by"
    vessel_types ||--o{ vessels : "vessel_type_id"
    vessels ||--o{ catch_declarations : "vessel_id"
    vessels ||--o{ check_counters : "vessel_id"
    vessels ||--o{ eez_declarations : "vessel_id"
    vessels ||--o{ entry_exit_alerts : "vessel_id"
    vessels ||--o{ external_inspections : "vessel_id"
    vessels ||--o{ external_list_vessel : "vessel_id"
    vessels ||--o{ infractions : "vessel_id"
    vessels ||--o{ inspections : "vessel_id"
    vessels ||--o{ internal_notes : "vessel_id"
    vessels ||--o{ landings : "vessel_id"
    vessels ||--o{ licenses : "vessel_id"
    vessels ||--o{ logbook_entry_vessel : "vessel_id"
    vessels ||--o{ missed_declarations : "vessel_id"
    vessels ||--o{ mission_vessel : "vessel_id"
    vessels ||--o{ movements : "vessel_id"
    vessels ||--o{ mrep_dpeps : "vessel_id"
    vessels ||--o{ mrep_gies_refusals : "vessel_id"
    vessels ||--o{ observations : "vessel_id"
    vessels ||--o{ operation_vessel : "vessel_id"
    vessels ||--o{ positions : "vessel_id"
    vessels ||--o{ sent_messages : "vessel_id"
    vessels ||--o{ standing_instructions : "vessel_id"
    vessels ||--o{ surveillance_observations : "vessel_id"
    vessels ||--o{ tracking_devices : "vessel_id"
    vessels ||--o{ transshipments : "partner_vessel_id"
    vessels ||--o{ transshipments : "vessel_id"
    vessels ||--o{ vessel_actors : "vessel_id"
    vessels ||--o{ vessel_current_states : "vessel_id"
    vessels ||--o{ vessel_events : "vessel_id"
    vessels ||--o{ vessel_field_provenance : "vessel_id"
    vessels ||--o{ vessel_history : "vessel_id"
    vessels ||--o{ vessel_identifiers : "vessel_id"
    vessels ||--o{ vessel_sources : "candidate_vessel_id"
    vessels ||--o{ vessel_sources : "vessel_id"
    vessels ||--o{ watch_entries : "vessel_id"
    zones ||--o{ catch_declarations : "zone_id"
    zones ||--o{ missions : "zone_id"
    zones ||--o{ observations : "zone_id"
    zones ||--o{ permission_grants : "zone_id"
    zones ||--o{ ports : "zone_id"
    zones ||--o{ positions : "zone_id"
    zones ||--o{ vessel_events : "zone_id"
    zones ||--o{ zone_counts : "zone_id"
    zones ||--o{ zone_rules : "zone_id"
```
