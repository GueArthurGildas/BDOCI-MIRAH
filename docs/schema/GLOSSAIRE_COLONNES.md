# Glossaire des colonnes

Correspondance colonne par colonne entre le schéma v1 (`database/docs/BOID_MIRAH_schema.sql`, français) et le schéma v2 (`docs/schema/schema_v2.sql`, anglais). Les colonnes `created_at` et `updated_at` ajoutées par convention (voir `CONVENTIONS.md`) ne figurent pas dans les tableaux. Les noms français sont aussi dans les commentaires de la base (`COMMENT ON COLUMN`).

Ne pas éditer à la main : en cas de changement de nom, corriger ensemble le schéma v2 (`schema_v2.sql`) et ce document.

## Noyau

### `module_registry` (spécification : `module_registre`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(30) |
| `libelle` | `label` | varchar(120) |
| `version` | `version` | varchar(20) |
| `actif` | `is_active` | boolean |
| `dependances` | `dependencies` | jsonb |
| `created_at` | `created_at` | timestamp(0) |
| `updated_at` | `updated_at` | timestamp(0) |

### `countries` (spécification : `pays`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code_iso3` | `iso3_code` | char(3) |
| `libelle` | `label` | varchar(100) |

### `zones` (spécification : `zone`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `parent_id` | `parent_id` | bigint |
| `code` | `code` | varchar(30) |
| `libelle` | `label` | varchar(120) |
| `type` | `type` | varchar(40) |
| `geometrie` | `geometry` | geometry(Geometry, 4326) |
| `actif` | `is_active` | boolean |

### `teams` (spécification : `equipe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `libelle` | `label` | varchar(80) |
| `chef_utilisateur_id` | `lead_user_id` | bigint |
| `actif` | `is_active` | boolean |

### `roles` (spécification : `role`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `libelle` | `label` | varchar(80) |
| `parent_id` | `parent_id` | bigint |
| `systeme` | `is_system` | boolean |

### `permission_groups` (spécification : `groupe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(60) |
| `libelle` | `label` | varchar(120) (nullable) |

### `users` (spécification : `utilisateur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `type` | `type` | varchar(40) |
| `nom` | `name` | varchar(120) |
| `fonction` | `job_title` | varchar(120) |
| `email` | `email` | varchar(190) |
| `telephone` | `phone` | varchar(40) |
| `numero_whatsapp` | `whatsapp_number` | varchar(40) |
| `mot_de_passe` | `password` | varchar(255) |
| `actif` | `is_active` | boolean |
| `derniere_connexion` | `last_login_at` | timestamp(0) |
| `created_at` | `created_at` | timestamp(0) |
| `updated_at` | `updated_at` | timestamp(0) |

### `role_user` (spécification : `utilisateur_role`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `utilisateur_id` | `user_id` | bigint |
| `role_id` | `role_id` | bigint |

### `permission_group_user` (spécification : `utilisateur_groupe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `utilisateur_id` | `user_id` | bigint |
| `groupe_id` | `permission_group_id` | bigint |

### `team_user` (spécification : `utilisateur_equipe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `utilisateur_id` | `user_id` | bigint |
| `equipe_id` | `team_id` | bigint |

### `action_catalog` (spécification : `action_catalogue`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `module_id` | `module_id` | bigint |
| `code` | `code` | varchar(80) |
| `verbe` | `verb` | varchar(40) |
| `agent_ia_possible` | `ai_agent_allowed` | boolean |
| `agent_ia_actif` | `ai_agent_enabled` | boolean |

### `permission_grants` (spécification : `permission`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `role_id` | `role_id` | bigint |
| `groupe_id` | `permission_group_id` | bigint |
| `action_id` | `action_id` | bigint |
| `zone_id` | `zone_id` | bigint |
| `valide_du` | `valid_from` | date |
| `valide_au` | `valid_to` | date |

### `audit_log` (spécification : `journal_audit`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `date_action` | `acted_at` | timestamp(3) |
| `utilisateur_id` | `user_id` | bigint |
| `demande_par_utilisateur_id` | `requested_by_user_id` | bigint |
| `action_code` | `action_code` | varchar(80) |
| `objet_type` | `object_type` | varchar(80) |
| `objet_id` | `object_id` | bigint |
| `avant` | `before` | jsonb |
| `apres` | `after` | jsonb |
| `ip` | `ip_address` | varchar(45) |

### `documents` (spécification : `document`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `nom` | `name` | varchar(255) |
| `type_mime` | `mime_type` | varchar(100) |
| `chemin` | `path` | varchar(500) |
| `empreinte_sha256` | `sha256_fingerprint` | char(64) |
| `fige` | `is_frozen` | boolean |
| `depose_par` | `filed_by` | bigint |
| `created_at` | `created_at` | timestamp(0) |

### `document_links` (spécification : `document_lien`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `document_id` | `document_id` | bigint |
| `objet_type` | `object_type` | varchar(80) |
| `objet_id` | `object_id` | bigint |

### `comments` (spécification : `commentaire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `objet_type` | `object_type` | varchar(80) |
| `objet_id` | `object_id` | bigint |
| `utilisateur_id` | `user_id` | bigint |
| (absente de la spécification) | `kind` | varchar(40) |
| `texte` | `body` | text |
| `created_at` | `created_at` | timestamp(0) |

### `reference_lists` (spécification : `liste_reference`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(60) |
| `libelle` | `label` | varchar(120) |

### `reference_list_values` (spécification : `liste_valeur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `liste_id` | `reference_list_id` | bigint |
| `code` | `code` | varchar(60) |
| `libelle` | `label` | varchar(160) |
| `ordre` | `sort_order` | integer |
| `actif` | `is_active` | boolean |

### `custom_field_definitions` (spécification : `champ_definition`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `objet_type` | `object_type` | varchar(80) |
| `code` | `code` | varchar(60) |
| `libelle` | `label` | varchar(120) |
| `type` | `type` | varchar(40) |
| `visible` | `is_visible` | boolean |

### `custom_field_values` (spécification : `champ_valeur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `champ_id` | `custom_field_definition_id` | bigint |
| `objet_id` | `object_id` | bigint |
| `valeur` | `value` | text |

### `threshold_parameters` (spécification : `parametre_seuil`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(80) |
| `valeur` | `value` | varchar(120) |
| `unite` | `unit` | varchar(20) |
| `module_id` | `module_id` | bigint |

## M1 Référentiels et navires

### `institutions` (spécification : `organisme`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `type` | `type` | varchar(40) |
| `nom` | `name` | varchar(160) |
| `pays_id` | `country_id` | bigint |

### `ports` (spécification : `port`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(20) |
| `nom` | `name` | varchar(120) |
| `zone_id` | `zone_id` | bigint |
| `designe` | `is_designated` | boolean |

### `legal_entities` (spécification : `personne_morale`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `nom` | `name` | varchar(190) |
| `adresse` | `address` | varchar(255) |
| `telephone` | `phone` | varchar(40) |
| `email` | `email` | varchar(190) |
| `pays_id` | `country_id` | bigint |

### `people` (spécification : `personne`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `nom` | `name` | varchar(190) |
| `prenom` | `first_name` | varchar(120) |
| `nationalite_id` | `nationality_country_id` | bigint |
| `adresse` | `address` | varchar(255) |
| `telephone` | `phone` | varchar(40) |
| `email` | `email` | varchar(190) |
| `personne_morale_id` | `legal_entity_id` | bigint |

### `vessels` (spécification : `navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `nom` | `name` | varchar(190) |
| `statut_fiche` | `record_status` | varchar(40) |
| `origine_creation` | `creation_origin` | varchar(40) |
| `categorie` | `category` | varchar(40) |
| `type_navire_id` | `vessel_type_id` | bigint |
| `type_flotte` | `fleet_type` | varchar(40) |
| `thonier` | `is_tuna_vessel` | boolean |
| `statut_juridique` | `legal_status` | varchar(40) |
| `pavillon_id` | `flag_country_id` | bigint |
| `port_attache_id` | `home_port_id` | bigint |
| `annee_construction` | `construction_year` | smallint |
| `longueur_m` | `length_m` | numeric(6,2) |
| `largeur_m` | `beam_m` | numeric(6,2) |
| `creux_sur_quille_m` | `depth_over_keel_m` | numeric(6,2) |
| `jauge_nette` | `net_tonnage` | numeric(10,2) |
| `jauge_brute` | `gross_tonnage` | numeric(10,2) |
| `motorisation` | `propulsion` | varchar(120) |
| `capacite_cales_m3` | `hold_capacity_m3` | numeric(10,2) |
| `certificat_immatriculation` | `registration_certificate` | varchar(80) |
| `identifiant_fao` | `fao_identifier` | varchar(40) |
| `licence_non_renseignee` | `license_not_provided` | boolean |
| `created_at` | `created_at` | timestamp(0) |
| `updated_at` | `updated_at` | timestamp(0) |

### `vessel_identifiers` (spécification : `navire_identifiant`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `type` | `type` | varchar(40) |
| `valeur` | `value` | varchar(60) |
| `valide_du` | `valid_from` | date |
| `valide_au` | `valid_to` | date |

### `vessel_history` (spécification : `navire_historique`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `ancien_nom` | `former_name` | varchar(190) |
| `ancien_pavillon_id` | `former_flag_country_id` | bigint |
| `date_changement` | `changed_on` | date |

### `vessel_sources` (spécification : `navire_source`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `source` | `source` | varchar(40) |
| `identifiant_externe` | `external_id` | varchar(120) |
| `derniere_import` | `last_import_at` | timestamp(0) |
| `donnees_brutes` | `raw_data` | jsonb |
| `statut_rapprochement` | `matching_status` | varchar(40) |
| `navire_candidat_id` | `candidate_vessel_id` | bigint |
| `critere_rapprochement` | `matching_criterion` | varchar(40) |
| `tranche_par` | `settled_by` | bigint |
| `tranche_le` | `settled_at` | timestamp(0) |

### `vessel_field_provenance` (spécification : `navire_champ_provenance`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `champ` | `field` | varchar(60) |
| `source` | `source` | varchar(40) |
| `mis_a_jour_le` | `updated_at` | timestamp(0) |

### `source_priorities` (spécification : `source_priorite`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `champ` | `field` | varchar(60) |
| `source` | `source` | varchar(40) |
| `rang` | `rank` | smallint |

### `vessel_actors` (spécification : `navire_acteur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `role` | `role` | varchar(40) |
| `personne_morale_id` | `legal_entity_id` | bigint |
| `personne_id` | `person_id` | bigint |
| `debut` | `started_on` | date |
| `fin` | `ended_on` | date |

### `zone_rules` (spécification : `regle_de_zone`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `zone_id` | `zone_id` | bigint |
| `segment` | `segment` | varchar(40) |
| `milles` | `nautical_miles` | smallint |

### `species` (spécification : `espece`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code_fao` | `code_fao` | varchar(10) |
| `libelle` | `label` | varchar(160) |

### `gears` (spécification : `engin`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code_fao` | `code_fao` | varchar(10) |
| `libelle` | `label` | varchar(160) |

### `presentations` (spécification : `presentation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(10) |
| `libelle` | `label` | varchar(160) |
| `facteur_conversion` | `conversion_factor` | numeric(6,3) |

### `vessel_types` (spécification : `type_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(10) |
| `libelle` | `label` | varchar(160) |

### `external_lists` (spécification : `liste_externe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `organisme_id` | `institution_id` | bigint |
| `type` | `type` | varchar(40) |
| `libelle` | `label` | varchar(160) |

### `external_list_vessel` (spécification : `navire_liste_externe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `liste_id` | `external_list_id` | bigint |
| `depuis` | `listed_from` | date |
| `jusqu_a` | `listed_until` | date |

### `contacts` (spécification : `contact`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `categorie` | `category` | varchar(80) |
| `pays_id` | `country_id` | bigint |
| `organisme_id` | `institution_id` | bigint |
| `structure` | `organization` | varchar(160) |
| `email` | `email` | varchar(190) |
| `telephone` | `phone` | varchar(40) |

### `agents` (spécification : `agent`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `utilisateur_id` | `user_id` | bigint |
| `nom` | `name` | varchar(190) |
| `grade` | `grade` | varchar(80) |
| `administration` | `agency` | varchar(120) |
| `est_inspecteur` | `is_inspector` | boolean |

### `accreditations` (spécification : `habilitation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `agent_id` | `agent_id` | bigint |
| `numero_carte` | `card_number` | varchar(60) |
| `decision` | `decision` | varchar(120) |
| `date_restitution` | `returned_on` | date |
| `formations` | `trainings` | text |

### `vessel_events` (spécification : `evenement_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `date_evenement` | `occurred_at` | timestamp(0) |
| `type` | `type` | varchar(60) |
| `module_id` | `module_id` | bigint |
| `objet_type` | `object_type` | varchar(80) |
| `objet_id` | `object_id` | bigint |
| `resume` | `summary_key`, `summary_params` | varchar(120), jsonb |
| `zone_id` | `zone_id` | bigint |
| `utilisateur_id` | `user_id` | bigint |
| `source` | `source` | varchar(30) |

### `vessel_current_states` (spécification : `etat_courant_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `licence_statut` | `license_status` | varchar(40) |
| `licence_expire_le` | `license_expires_on` | date |
| `avarie_vms_en_cours` | `vms_failure_ongoing` | boolean |
| `derniere_position_le` | `last_position_at` | timestamp(0) |
| `silence_vms_heures` | `vms_silence_hours` | numeric(8,2) |
| `suivi_renforce` | `is_enhanced_monitoring` | boolean |
| `infractions_ouvertes` | `open_infractions_count` | integer |
| `nb_verifications` | `checks_count` | integer |
| `statut_feu` | `status_light` | varchar(40) |
| `mis_a_jour_le` | `updated_at` | timestamp(0) |

### `data_sources` (spécification : `etat_connecteur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `mode` | `mode` | varchar(40) |
| `derniere_synchro` | `last_synced_at` | timestamp(0) |
| `statut_voyant` | `indicator_status` | varchar(40) |
| `derniere_erreur` | `last_error` | text |
| `donnees_perimees` | `is_stale` | boolean |

### `data_source_syncs` (spécification : `journal_synchro`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `connecteur_id` | `data_source_id` | bigint |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `finished_at` | timestamp(0) |
| `nb_recus` | `received_count` | integer |
| `nb_rejetes` | `rejected_count` | integer |
| `message` | `message` | text |

### `data_source_rejections` (spécification : `rejet_import`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `connecteur_id` | `data_source_id` | bigint |
| `synchro_id` | `data_source_sync_id` | bigint |
| `donnees` | `data` | jsonb |
| `motif` | `reason` | varchar(255) |
| `traite` | `is_handled` | boolean |
| `traite_par` | `handled_by` | bigint |

### `external_links` (spécification : `lien_externe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `source` | `source` | varchar(40) |
| `modele_url` | `model_url` | varchar(500) |
| `mode_repli_copie` | `is_copy_fallback` | boolean |

## M2 Suivi des licences

### `licenses` (spécification : `licence`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `numero` | `number` | varchar(80) |
| `segment` | `segment` | varchar(40) |
| `date_emission` | `issued_on` | date |
| `date_expiration` | `expires_on` | date |
| `statut` | `status` | varchar(40) |
| `saisie_par` | `entered_by` | bigint |
| `origine` | `origin` | varchar(40) |

### `license_zone` (spécification : `licence_zone`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `licence_id` | `license_id` | bigint |
| `zone_id` | `zone_id` | bigint |

### `gear_license` (spécification : `licence_engin`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `licence_id` | `license_id` | bigint |
| `engin_id` | `gear_id` | bigint |

### `license_species` (spécification : `licence_espece`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `licence_id` | `license_id` | bigint |
| `espece_id` | `species_id` | bigint |

### `license_conditions` (spécification : `licence_condition`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `licence_id` | `license_id` | bigint |
| `texte` | `body` | varchar(500) |

### `license_documents` (spécification : `licence_document`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `document_id` | `document_id` | bigint |
| `licence_id` | `license_id` | bigint |
| `navire_id` | `vessel_id` | bigint |

### `fnp_statuses` (spécification : `statut_fnp`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `statut` | `status` | varchar(40) |
| `motif_radiation` | `deregistration_reason` | varchar(40) |
| `date_statut` | `status_on` | date |

### `license_suspensions` (spécification : `suspension_licence`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `licence_id` | `license_id` | bigint |
| `motif` | `reason` | varchar(40) |
| `debut` | `started_on` | date |
| `fin_prevue` | `planned_end_on` | date |
| `levee_le` | `lifted_on` | date |
| `dossier_infraction_id` | `infraction_case_id` | bigint |
| `avarie_vms_id` | `vms_failure_id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `propose_par` | `proposed_by` | bigint |
| `decide_par` | `decided_by` | bigint |

### `license_revocations` (spécification : `revocation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `licence_id` | `license_id` | bigint |
| `motif` | `reason` | varchar(255) |
| `date_revocation` | `revoked_on` | date |
| `decide_par` | `decided_by` | bigint |
| `dossier_infraction_id` | `infraction_case_id` | bigint |
| `recidive` | `is_repeat_offense` | boolean |

### `vms_compatibility_certificates` (spécification : `certificat_compatibilite_vms`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `date_emission` | `issued_on` | date |
| `date_expiration` | `expires_on` | date |

### `external_authorizations` (spécification : `autorisation_externe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `organisme_id` | `institution_id` | bigint |
| `numero` | `number` | varchar(80) |
| `valide_du` | `valid_from` | date |
| `valide_au` | `valid_to` | date |

### `license_imports` (spécification : `import_licences`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `document_id` | `document_id` | bigint |
| `statut` | `status` | varchar(40) |
| `cree_par` | `created_by` | bigint |
| `valide_le` | `validated_at` | timestamp(0) |

### `license_import_lines` (spécification : `import_licences_ligne`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `import_id` | `license_import_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `donnees` | `data` | jsonb |
| `statut` | `status` | varchar(40) |
| `motif_rejet` | `rejection_reason` | varchar(255) |
| `licence_id` | `license_id` | bigint |

### `license_registers` (spécification : `registre_licences`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `annee` | `year` | smallint |
| `document_id` | `document_id` | bigint |
| `genere_le` | `generated_at` | timestamp(0) |

### `authorized_list_publications` (spécification : `publication_liste_autorises`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `version` | `version` | integer |
| `document_id` | `document_id` | bigint |
| `publiee_le` | `published_at` | timestamp(0) |

## M3 VMS, AIS, ERS

### `beacons` (spécification : `balise_vms`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `numero_serie` | `serial_number` | varchar(80) |
| `modele` | `model` | varchar(80) |
| `fabricant` | `manufacturer` | varchar(80) |
| `fournisseur` | `supplier` | varchar(80) |
| `modem` | `modem` | varchar(40) |
| `abonnement` | `subscription` | varchar(80) |

### `beacon_assignments` (spécification : `balise_affectation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `balise_id` | `beacon_id` | bigint |
| `debut` | `started_on` | date |
| `fin` | `ended_on` | date |
| `motif_remplacement` | `replacement_reason` | varchar(255) |

### `approved_installers` (spécification : `installateur_agree`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `personne_morale_id` | `legal_entity_id` | bigint |
| `agrement_jusqu_a` | `approved_until` | date |

### `vms_assessment_requests` (spécification : `demande_evaluation_vms`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `date_demande` | `requested_on` | date |
| `statut` | `status` | varchar(40) |

### `new_beacon_requests` (spécification : `demande_nouvelle_balise`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `date_demande` | `requested_on` | date |
| `statut` | `status` | varchar(40) |

### `compatibility_assessments` (spécification : `evaluation_compatibilite`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `demande_evaluation_id` | `assessment_request_id` | bigint |
| `resultat` | `result` | varchar(40) |
| `date_evaluation` | `assessed_on` | date |

### `beacon_installations` (spécification : `installation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `demande_id` | `new_beacon_request_id` | bigint |
| `installateur_id` | `installer_id` | bigint |
| `date_installation` | `installed_on` | date |
| `pv_document_id` | `report_document_id` | bigint |

### `vessel_reporting_obligations` (spécification : `obligation_declarative_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `vms` | `requires_vms` | boolean |
| `ais` | `requires_ais` | boolean |
| `ers` | `requires_ers` | boolean |
| `zee` | `requires_eez_declaration` | boolean |
| `journal` | `requires_fishing_log` | boolean |

### `positions` (spécification : `position`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `source` | `source` | varchar(40) |
| `date_emission` | `emitted_at` | timestamp(0) |
| `date_reception` | `received_at` | timestamp(0) |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `vitesse` | `speed` | numeric(5,2) |
| `cap` | `course` | smallint |
| `activite` | `activity` | varchar(40) |
| `statut_navigation` | `navigation_status` | varchar(40) |
| `precision_m` | `accuracy_m` | integer |
| `zone_id` | `zone_id` | bigint |
| `motif_conservation` | `retention_reason` | varchar(60) |

### `position_daily_summaries` (spécification : `position_jour`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `jour` | `day` | date |
| `nb_positions` | `positions_count` | integer |
| `heures_emission` | `emission_hours` | numeric(5,2) |
| `distance_milles` | `distance_nautical_miles` | numeric(8,2) |
| `vitesse_moyenne` | `average_speed` | numeric(5,2) |
| `zones` | `zones` | jsonb |

### `control_slots` (spécification : `creneau_controle`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `equipe_id` | `team_id` | bigint |
| `operateur_id` | `operator_user_id` | bigint |

### `slot_positions` (spécification : `position_creneau`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `creneau_id` | `control_slot_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `vitesse` | `speed` | numeric(5,2) |
| `zone_id` | `zone_id` | bigint |
| `localite_id` | `locality_id` | bigint |
| `derniere_emission` | `last_emission_at` | timestamp(0) |
| `test_emission` | `emission_test` | varchar(40) |
| `test_vitesse_activite` | `speed_activity_test` | varchar(40) |
| `test_trajectoire` | `trajectory_test` | varchar(40) |
| `resultat` | `result` | varchar(40) |
| `confirme_par` | `confirmed_by` | bigint |
| `source_chargement` | `loading_source` | varchar(40) |

### `tracks` (spécification : `trace`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `geometrie` | `geometry` | geometry(LineString, 4326) |

### `screen_captures` (spécification : `capture_ecran`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `document_id` | `document_id` | bigint |
| `pris_le` | `taken_at` | timestamp(0) |

### `identity_anomalies` (spécification : `anomalie_identite`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `detail` | `detail` | varchar(255) |
| `detectee_le` | `detected_at` | timestamp(0) |

### `emission_cutoffs` (spécification : `coupure_emission`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `arret_technique` | `is_technical_stop` | boolean |

### `alert_rules` (spécification : `regle_alerte`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `type` | `type` | varchar(40) |
| `seuil_heures` | `threshold_hours` | numeric(5,2) |
| `parametres` | `parameters` | jsonb |
| `actif` | `is_active` | boolean |

### `alerts` (spécification : `alerte`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `regle_id` | `alert_rule_id` | bigint |
| `type` | `type` | varchar(40) |
| `zone_id` | `zone_id` | bigint |
| `declenchee_le` | `triggered_at` | timestamp(0) |
| `statut` | `status` | varchar(40) |
| `acquittee_par` | `acknowledged_by` | bigint |
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `operation_id` | `operation_id` | bigint |

### `alert_handlings` (spécification : `traitement_alerte`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `alerte_id` | `alert_id` | bigint |
| `questions_verification` | `verification_questions` | jsonb |
| `navire_contacte` | `is_vessel_contacted` | boolean |
| `mesures` | `measures` | text |
| `traite_par` | `handled_by` | bigint |
| `traite_le` | `handled_at` | timestamp(0) |

### `vms_failures` (spécification : `avarie_vms`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `categorie` | `category` | varchar(40) |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `frequence_rapport_h` | `report_frequency_h` | smallint |
| `delai_reparation_j` | `repair_deadline_days` | smallint |
| `operation_id` | `operation_id` | bigint |

### `vms_cutoff_authorizations` (spécification : `autorisation_coupure_vms`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `motif` | `reason` | varchar(255) |

### `manual_position_reports` (spécification : `rapport_position_manuel`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `avarie_id` | `vms_failure_id` | bigint |
| `recu_le` | `received_at` | timestamp(0) |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `saisi_par` | `entered_by` | bigint |

### `working_order_attestations` (spécification : `attestation_bon_fonctionnement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `avarie_id` | `vms_failure_id` | bigint |
| `document_id` | `document_id` | bigint |
| `date_attestation` | `attested_on` | date |

### `cessation_orders` (spécification : `ordre_cessation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `avarie_id` | `vms_failure_id` | bigint |
| `date_ordre` | `ordered_at` | timestamp(0) |
| `suspension` | `includes_suspension` | boolean |
| `retour_port` | `requires_port_return` | boolean |
| `donne_par` | `given_by` | bigint |

### `sweeps` (spécification : `balayage`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `creneau_id` | `control_slot_id` | bigint |
| `operateur_id` | `operator_user_id` | bigint |
| `nb_navires` | `vessels_count` | integer |
| `nb_non_emetteurs` | `non_emitting_count` | integer |

### `sweep_anomalies` (spécification : `anomalie_balayage`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `balayage_id` | `sweep_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `type` | `type` | varchar(40) |

### `monthly_aggregates` (spécification : `agregation_mensuelle`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `annee_mois` | `year_month` | char(7) |
| `navire_id` | `vessel_id` | bigint |
| `taux_emission` | `emission_rate` | numeric(5,2) |
| `nb_silences` | `silences_count` | integer |

### `ers_declarations` (spécification : `declaration_ers`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `date_declaration` | `declared_at` | timestamp(0) |
| `source` | `source` | varchar(40) |

### `ers_catch_lines` (spécification : `ers_ligne_capture`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `declaration_id` | `ers_declaration_id` | bigint |
| `espece_id` | `species_id` | bigint |
| `presentation_id` | `presentation_id` | bigint |
| `facteur_conversion` | `conversion_factor` | numeric(6,3) |
| `poids_vif_kg` | `live_weight_kg` | numeric(12,2) |
| `zone_fao` | `fao_zone` | varchar(10) |
| `engin_id` | `gear_id` | bigint |

## M4 Déclarations d'entrée et de sortie de la ZEE

### `eez_declarations` (spécification : `declaration_zee`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `type` | `type` | varchar(40) |
| `recue_le` | `received_at` | timestamp(0) |
| `moyen_id` | `reception_channel_id` | bigint |
| `origine_saisie` | `entry_origin` | varchar(40) |
| `saisi_par` | `entered_by` | bigint |
| `passage_le` | `crossed_at` | timestamp(0) |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `route` | `route` | varchar(120) |
| `vitesse` | `speed` | numeric(5,2) |
| `destination` | `destination` | varchar(160) |
| `motif` | `reason` | varchar(40) |
| `nom_declare` | `declared_name` | varchar(190) |
| `imo_declare` | `declared_imo` | varchar(20) |
| `mmsi_declare` | `declared_mmsi` | varchar(20) |
| `ircs_declare` | `declared_ircs` | varchar(20) |
| `pavillon_declare` | `declared_flag` | varchar(80) |
| `proprietaire_declare` | `declared_owner` | varchar(190) |
| `statut` | `status` | varchar(40) |

### `eez_declaration_catches` (spécification : `declaration_zee_capture`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `declaration_id` | `eez_declaration_id` | bigint |
| `espece_id` | `species_id` | bigint |
| `poids_kg` | `weight_kg` | numeric(12,2) |

### `entry_exit_links` (spécification : `lien_entree_sortie`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `declaration_entree_id` | `entry_declaration_id` | bigint |
| `declaration_sortie_id` | `exit_declaration_id` | bigint |
| `ecart_kg` | `variance_kg` | numeric(12,2) |
| `explication` | `explanation` | varchar(40) |

### `eez_declaration_checks` (spécification : `verification_zee`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `declaration_id` | `eez_declaration_id` | bigint |
| `controle` | `check_type` | varchar(40) |
| `resultat` | `result` | varchar(40) |
| `commentaire` | `comment` | varchar(255) |

### `missed_declarations` (spécification : `non_declaration`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `detectee_le` | `detected_at` | timestamp(0) |
| `relances` | `reminders` | integer |
| `qualification` | `qualification` | varchar(120) |

### `entry_exit_alerts` (spécification : `alerte_entree_sortie`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `declaration_id` | `eez_declaration_id` | bigint |
| `sens` | `direction` | varchar(40) |
| `date_alerte` | `alerted_at` | timestamp(0) |
| `statut` | `status` | varchar(40) |

## M5 Opérations, main courante, surveillance de quart

### `operation_categories` (spécification : `categorie_operation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `libelle` | `label` | varchar(80) |

### `operation_counters` (spécification : `compteur_operation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `categorie_id` | `category_id` | bigint |
| `annee` | `year` | smallint |
| `dernier_numero` | `last_number` | integer |

### `operations` (spécification : `operation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(190) |
| `categorie_id` | `category_id` | bigint |
| `numero` | `number` | integer |
| `annee` | `year` | smallint |
| `nom` | `name` | varchar(120) |
| `statut` | `status` | varchar(40) |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `origine` | `origin` | varchar(60) |
| `resume` | `summary` | text |
| `resultat` | `result` | text |
| `cree_par` | `created_by` | bigint |
| `cloture_par` | `closed_by` | bigint |

### `operation_contexts` (spécification : `operation_contexte`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `operation_id` | `operation_id` | bigint |
| `premier_evenement` | `first_event` | text |
| `temperature` | `temperature` | numeric(4,1) |
| `pression` | `pressure` | numeric(6,1) |
| `visibilite` | `visibility` | varchar(40) |
| `nebulosite` | `cloud_cover` | varchar(40) |
| `vent_beaufort` | `wind_beaufort` | smallint |
| `etat_mer` | `sea_state` | varchar(40) |

### `operation_vessel` (spécification : `operation_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `operation_id` | `operation_id` | bigint |
| `navire_id` | `vessel_id` | bigint |

### `operation_user` (spécification : `operation_personnel`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `operation_id` | `operation_id` | bigint |
| `utilisateur_id` | `user_id` | bigint |
| `role` | `role` | varchar(60) |

### `operation_events` (spécification : `operation_evenement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `date_evt` | `occurred_at` | timestamp(0) |
| `texte` | `body` | text |

### `operation_instructions` (spécification : `operation_instruction`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `texte` | `body` | text |
| `donnee_par` | `given_by` | bigint |

### `logbook_days` (spécification : `main_courante_jour`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `jour` | `day` | date |
| `debut_quart` | `shift_start_at` | timestamp(0) |
| `fin_quart` | `shift_end_at` | timestamp(0) |
| `statut` | `status` | varchar(40) |
| `document_pdf_id` | `pdf_document_id` | bigint |
| `verrouillee_le` | `locked_at` | timestamp(0) |

### `logbook_closings` (spécification : `cloture_mc`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `main_courante_id` | `logbook_day_id` | bigint |
| `compteurs` | `counters` | jsonb |
| `cloturee_par` | `closed_by` | bigint |
| `cloturee_le` | `closed_at` | timestamp(0) |

### `logbook_entries` (spécification : `mc_evenement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `main_courante_id` | `logbook_day_id` | bigint |
| `quand` | `occurred_at` | timestamp(0) |
| `comment_id` | `channel_id` | bigint |
| `quoi` | `content` | text |
| `sens` | `direction` | varchar(40) |
| `est_ordre_ou_decision` | `is_order_or_decision` | boolean |
| `operation_id` | `operation_id` | bigint |
| `statut_traitement` | `handling_status` | varchar(40) |
| `rectifie_evenement_id` | `corrects_entry_id` | bigint |
| `saisi_par` | `entered_by` | bigint |

### `logbook_entry_vessel` (spécification : `mc_evenement_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `navire_id` | `vessel_id` | bigint |

### `rotations` (spécification : `rotation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `equipe_id` | `team_id` | bigint |
| `jour` | `day` | date |
| `heure_debut` | `start_time` | time(0) |
| `heure_fin` | `end_time` | time(0) |

### `shift_handovers` (spécification : `releve_quart`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `rotation_id` | `rotation_id` | bigint |
| `prise_le` | `started_at` | timestamp(0) |

### `surveillance_observations` (spécification : `observation_surveillance`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `position_creneau_id` | `slot_position_id` | bigint |
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `texte` | `body` | text |
| `statut` | `status` | varchar(40) |
| `cree_par` | `created_by` | bigint |
| `cree_le` | `created_at` | timestamp(0) |
| (absente) | `legacy_observation_id` | bigint, unique, ajout de l'implémentation |

### `relays` (spécification : `relais`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `releve_id` | `shift_handover_id` | bigint |
| `observation_id` | `surveillance_observation_id` | bigint |
| `equipe_suivante_id` | `next_team_id` | bigint |
| `accuse_lecture_par` | `read_acknowledged_by` | bigint |
| `accuse_le` | `acknowledged_at` | timestamp(0) |

### `standing_instructions` (spécification : `consigne_permanente`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `texte` | `body` | text |
| `debut` | `started_on` | date |
| `fin` | `ended_on` | date |

### `zone_counts` (spécification : `comptage_zone`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `creneau_id` | `control_slot_id` | bigint |
| `zone_id` | `zone_id` | bigint |
| `nb_navires` | `vessels_count` | integer |

### `consistency_rules` (spécification : `regle_coherence`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `description` | `description` | varchar(255) |
| `parametres` | `parameters` | jsonb |
| `actif` | `is_active` | boolean |

### `check_counters` (spécification : `compteur_verification`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `navire_id` | `vessel_id` | bigint |
| `nb` | `checks_count` | integer |
| `derniere_le` | `last_checked_at` | timestamp(0) |
| `alerte_levee` | `is_alert_raised` | boolean |

### `watch_entries` (spécification : `veille_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `origine` | `origin` | varchar(40) |
| `debut` | `started_at` | timestamp(0) |
| `statut` | `status` | varchar(40) |
| `clos_le` | `closed_at` | timestamp(0) |
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `alerte_entree_sortie_id` | `entry_exit_alert_id` | bigint |
| `decide_par` | `decided_by` | bigint |

## M6 MREP (miroir en lecture seule)

### `mrep_dpeps` (spécification : `mrep_dpep`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `id_externe` | `external_id` | varchar(80) |
| `navire_id` | `vessel_id` | bigint |
| `numero` | `number` | varchar(80) |
| `port_id` | `port_id` | bigint |
| `arrivee_prevue` | `planned_arrival_at` | timestamp(0) |
| `objet` | `purpose` | varchar(40) |
| `derniere_escale` | `last_port_call` | varchar(160) |
| `captures_a_bord` | `catches_on_board` | jsonb |
| `captures_a_debarquer` | `catches_to_land` | jsonb |
| `complete` | `is_complete` | boolean |
| `delai_72h_respecte` | `is_72h_deadline_met` | boolean |
| `brut` | `raw` | jsonb |
| `lu_le` | `read_at` | timestamp(0) |

### `mrep_entry_decisions` (spécification : `mrep_decision_entree`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dpep_id` | `dpep_id` | bigint |
| `decision` | `decision` | varchar(40) |
| `date_decision` | `decided_at` | timestamp(0) |
| `decideur` | `decision_maker` | varchar(160) |

### `mrep_port_calls` (spécification : `mrep_escale`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dpep_id` | `dpep_id` | bigint |
| `arrivee` | `arrival` | timestamp(0) |
| `depart` | `departure` | timestamp(0) |
| `inspectee` | `is_inspected` | boolean |
| `infraction_relevee` | `is_infraction_recorded` | boolean |
| `inspection_externe_id` | `inspection_id` | bigint |

### `mrep_notifications` (spécification : `mrep_notification`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dpep_id` | `dpep_id` | bigint |
| `destinataire` | `recipient` | varchar(190) |
| `canal` | `channel` | varchar(40) |
| `date_notification` | `notified_at` | timestamp(0) |
| `statut` | `status` | varchar(40) |

### `mrep_gies_refusals` (spécification : `mrep_refus_gies`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `reference` | `reference` | varchar(80) |
| `date_refus` | `refused_on` | date |

### `mrep_targetings` (spécification : `mrep_ciblage`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `annee` | `year` | smallint |
| `objectif_pct` | `objective_pct` | numeric(5,2) |
| `realise_pct` | `achieved_pct` | numeric(5,2) |
| `cible` | `target` | varchar(40) |

## M11 e-inspection (miroir en lecture seule)

### `external_inspections` (spécification : `inspection_externe`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `id_externe` | `external_id` | varchar(80) |
| `reference` | `reference` | varchar(80) |
| `navire_id` | `vessel_id` | bigint |
| `lieu` | `location_type` | varchar(40) |
| `port_id` | `port_id` | bigint |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `debut` | `started_at` | timestamp(0) |
| `fin` | `ended_at` | timestamp(0) |
| `type_controle` | `control_type` | varchar(60) |
| `resultat` | `result` | varchar(60) |
| `signature_capitaine` | `captain_signature_status` | varchar(40) |
| `copie_remise` | `is_copy_handed_over` | boolean |
| `brut` | `raw` | jsonb |
| `lu_le` | `read_at` | timestamp(0) |

### `inspection_documents` (spécification : `inspection_document`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `inspection_id` | `inspection_id` | bigint |
| `type` | `type` | varchar(40) |
| `conforme` | `is_compliant` | boolean |

### `inspection_gears` (spécification : `inspection_engin`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `inspection_id` | `inspection_id` | bigint |
| `engin_id` | `gear_id` | bigint |
| `maille_mm` | `mesh_mm` | numeric(6,1) |
| `marquage_conforme` | `is_marking_compliant` | boolean |
| `conforme` | `is_compliant` | boolean |

### `declared_observed_catches` (spécification : `capture_declaree_constatee`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `inspection_id` | `inspection_id` | bigint |
| `espece_id` | `species_id` | bigint |
| `presentation_id` | `presentation_id` | bigint |
| `poids_declare_kg` | `declared_weight_kg` | numeric(12,2) |
| `poids_constate_kg` | `observed_weight_kg` | numeric(12,2) |
| `poids_net_kg` | `net_weight_kg` | numeric(12,2) |
| `facteur_conversion` | `conversion_factor` | numeric(6,3) |
| `ecart_pct` | `variance_pct` | numeric(7,2) |
| `depasse_seuil_csp` | `exceeds_csp_threshold` | boolean |

### `inspection_infractions` (spécification : `inspection_infraction`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `inspection_id` | `inspection_id` | bigint |
| `disposition` | `provision` | varchar(255) |
| `sceau` | `stamp` | varchar(80) |
| `mesures` | `measures` | text |

### `landings` (spécification : `debarquement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `port_id` | `port_id` | bigint |
| `date_debarquement` | `landed_at` | timestamp(0) |
| `origine` | `origin` | varchar(40) |
| `lignes` | `lines` | jsonb |

### `transshipments` (spécification : `transbordement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `navire_partenaire_id` | `partner_vessel_id` | bigint |
| `date_transbordement` | `transshipped_at` | timestamp(0) |
| `origine` | `origin` | varchar(40) |
| `lignes` | `lines` | jsonb |

## M7 Surveillance participative

### `witnesses` (spécification : `temoin`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `nom` | `name` | varchar(190) |
| `categorie` | `category` | varchar(40) |
| `email` | `email` | varchar(190) |
| `telephone` | `phone` | varchar(40) |

### `witness_reports` (spécification : `signalement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `recu_le` | `received_at` | timestamp(0) |
| `temoin_id` | `witness_id` | bigint |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `canal` | `channel` | varchar(40) |
| `operation_id` | `operation_id` | bigint |
| `saisi_par` | `entered_by` | bigint |
| `statut` | `status` | varchar(40) |

### `reported_vessels` (spécification : `signalement_navire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `signalement_id` | `witness_report_id` | bigint |
| `rang` | `rank` | smallint |
| `navire_id` | `vessel_id` | bigint |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `nom` | `name` | varchar(190) |
| `pavillon` | `flag` | varchar(80) |
| `port_immatriculation` | `registration_port` | varchar(120) |
| `type` | `type` | varchar(80) |
| `ircs` | `ircs` | varchar(20) |
| `immatriculation` | `registration` | varchar(40) |
| `autres_marques` | `other_marks` | varchar(160) |
| `engin` | `gear` | varchar(80) |
| `activite_peche` | `is_fishing` | boolean |
| `route` | `route` | varchar(120) |
| `derive` | `is_drifting` | boolean |
| `ravitaillement` | `is_resupplying` | boolean |
| `transbordement` | `is_transshipping` | boolean |
| `description` | `description` | text |

### `report_usabilities` (spécification : `exploitabilite`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `signalement_id` | `witness_report_id` | bigint |
| `exploitable` | `is_usable` | boolean |
| `compte_rendu_entretien` | `interview_report` | text |

### `report_investigations` (spécification : `enquete`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `signalement_id` | `witness_report_id` | bigint |
| `elements` | `elements` | jsonb |
| `notes` | `notes` | text |

### `report_decisions` (spécification : `decision_signalement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `signalement_id` | `witness_report_id` | bigint |
| `decision` | `decision` | varchar(40) |
| `decide_par` | `decided_by` | bigint |
| `decide_le` | `decided_at` | timestamp(0) |

### `authorized_list_distributions` (spécification : `diffusion_liste_autorises`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `publication_id` | `authorized_list_publication_id` | bigint |
| `destinataire` | `recipient` | varchar(190) |
| `envoyee_le` | `sent_at` | timestamp(0) |

## M8 Infractions et suites

### `infraction_types` (spécification : `referentiel_infraction`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `libelle` | `label` | varchar(255) |
| `base_legale` | `legal_basis` | varchar(255) |
| `gravite` | `severity` | varchar(40) |
| `sanction_encourue` | `potential_penalty` | varchar(255) |
| `actif` | `is_active` | boolean |

### `infraction_cases` (spécification : `dossier_infraction`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `reference` | `reference` | varchar(60) |
| `navire_id` | `vessel_id` | bigint |
| `capitaine_id` | `captain_id` | bigint |
| `armateur_id` | `shipowner_id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `inspection_id` | `inspection_id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `alerte_id` | `alert_id` | bigint |
| `signalement_id` | `witness_report_id` | bigint |
| `qualification` | `qualification` | varchar(40) |
| `observations_capitaine` | `captain_observations` | text |
| `statut` | `status` | varchar(40) |
| `ouvert_par` | `opened_by` | bigint |
| `ouvert_le` | `opened_at` | timestamp(0) |

### `upheld_infractions` (spécification : `infraction_retenue`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `referentiel_id` | `infraction_type_id` | bigint |
| `retenue_le` | `upheld_at` | timestamp(0) |

### `evidence_items` (spécification : `preuve`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `description` | `description` | varchar(255) |
| `document_id` | `document_id` | bigint |

### `seals` (spécification : `scelle`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `numero` | `number` | varchar(60) |
| `pose_le` | `placed_at` | timestamp(0) |

### `precautionary_measures` (spécification : `mesure_conservatoire`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `type` | `type` | varchar(40) |
| `propose_par` | `proposed_by` | bigint |
| `decide_par` | `decided_by` | bigint |
| `decide_le` | `decided_at` | timestamp(0) |

### `seizures` (spécification : `saisie`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `objet` | `item` | varchar(255) |
| `decision` | `decision` | varchar(40) |

### `violation_reports` (spécification : `pv`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `numero` | `number` | varchar(60) |
| `notifie_le` | `notified_at` | timestamp(0) |
| `refus_signature` | `is_signature_refused` | boolean |
| `document_id` | `document_id` | bigint |

### `litigation_follow_ups` (spécification : `suite_contentieux`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `dossier_id` | `infraction_case_id` | bigint |
| `type` | `type` | varchar(40) |
| `ouverte_le` | `opened_at` | timestamp(0) |
| `close_le` | `closed_at` | timestamp(0) |

### `decisions` (spécification : `decision`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `suite_id` | `follow_up_id` | bigint |
| `libelle` | `label` | varchar(255) |
| `decide_par` | `decided_by` | bigint |
| `decide_le` | `decided_at` | timestamp(0) |
| `sanction` | `is_penalty` | boolean |

### `payments` (spécification : `paiement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `decision_id` | `decision_id` | bigint |
| `montant` | `amount` | numeric(14,2) |
| `devise` | `currency` | char(3) |
| `paye_le` | `paid_on` | date |

### `appeals` (spécification : `recours`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `decision_id` | `decision_id` | bigint |
| `depose_le` | `filed_on` | date |
| `issue` | `outcome` | varchar(255) |

## M9 Missions et moyens

### `missions` (spécification : `mission`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(20) |
| `nature` | `nature` | varchar(40) |
| `type` | `type` | varchar(40) |
| `origine` | `origin` | varchar(80) |
| `objectifs` | `objectives` | text |
| `debut_prevu` | `planned_start_at` | timestamp(0) |
| `fin_prevue` | `planned_end_at` | timestamp(0) |
| `debut_reel` | `actual_start_at` | timestamp(0) |
| `fin_reelle` | `actual_end_at` | timestamp(0) |
| `zones` | `zones` | jsonb |
| `points_gps` | `points_gps` | jsonb |
| `statut` | `status` | varchar(40) |
| `operation_id` | `operation_id` | bigint |
| `alerte_id` | `alert_id` | bigint |
| `signalement_id` | `witness_report_id` | bigint |
| `cree_par` | `created_by` | bigint |

### `mission_authorizations` (spécification : `mission_autorisation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `autorise_par` | `authorized_by` | bigint |
| `role_autorisant` | `authorizing_role` | varchar(40) |
| `autorise_le` | `authorized_at` | timestamp(0) |

### `mission_participants` (spécification : `mission_participant`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `agent_id` | `agent_id` | bigint |
| `role` | `role` | varchar(60) |
| `administration` | `agency` | varchar(120) |
| `grade` | `grade` | varchar(80) |
| `est_chef_mission` | `is_mission_lead` | boolean |

### `observers` (spécification : `observateur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `personne_id` | `person_id` | bigint |

### `observer_boardings` (spécification : `embarquement_observateur`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `observateur_id` | `observer_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `debut` | `started_on` | date |
| `fin` | `ended_on` | date |

### `assets` (spécification : `moyen`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `type` | `type` | varchar(40) |
| `libelle` | `label` | varchar(160) |
| `actif` | `is_active` | boolean |

### `mission_assets` (spécification : `mission_moyen`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `moyen_id` | `asset_id` | bigint |
| `heures_moteur` | `engine_hours` | numeric(8,1) |
| `milles` | `nautical_miles` | numeric(8,1) |
| `carburant_prevu_l` | `planned_fuel_l` | numeric(10,1) |
| `carburant_consomme_l` | `consumed_fuel_l` | numeric(10,1) |
| `etat` | `state` | varchar(60) |
| `panne` | `breakdown` | text |
| `budget` | `budget` | numeric(14,2) |

### `mission_briefings` (spécification : `mission_briefing`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `mission_id` | `mission_id` | bigint |
| `tenu_le` | `held_at` | timestamp(0) |
| `notes` | `notes` | text |

### `mission_preparations` (spécification : `mission_preparation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `mission_id` | `mission_id` | bigint |
| `check_list` | `checklist` | jsonb |

### `patrol_tracks` (spécification : `trace_patrouille`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `document_id` | `document_id` | bigint |
| `geometrie` | `geometry` | geometry(LineString, 4326) |
| `zones_traversees` | `zones_crossed` | jsonb |

### `observed_units` (spécification : `mission_unite_observee`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `niveau` | `level` | varchar(40) |
| `vue_par` | `sighting_source` | varchar(40) |
| `nom_releve` | `recorded_name` | varchar(190) |
| `marques_relevees` | `recorded_marks` | varchar(160) |
| `activite_observee` | `observed_activity` | varchar(160) |
| `latitude` | `latitude` | numeric(9,6) |
| `longitude` | `longitude` | numeric(9,6) |
| `observee_le` | `observed_at` | timestamp(0) |
| `inspection_externe_id` | `inspection_id` | bigint |

### `mission_events` (spécification : `mission_evenement`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `mission_id` | `mission_id` | bigint |
| `date_evt` | `occurred_at` | timestamp(0) |
| `texte` | `body` | text |

### `mission_debriefings` (spécification : `mission_debriefing`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `mission_id` | `mission_id` | bigint |
| `tenu_le` | `held_at` | timestamp(0) |
| `notes` | `notes` | text |

### `mission_reviews` (spécification : `mission_bilan`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `mission_id` | `mission_id` | bigint |
| `nb_agents` | `agents_count` | integer |
| `duree_heures` | `duration_hours` | numeric(8,1) |
| `milles` | `nautical_miles` | numeric(8,1) |
| `carburant_l` | `fuel_l` | numeric(10,1) |
| `unites_vues` | `sighted_units_count` | integer |
| `unites_approchees` | `approached_units_count` | integer |
| `unites_controlees` | `inspected_units_count` | integer |
| `conformes` | `compliant_count` | integer |
| `non_conformes` | `non_compliant_count` | integer |
| `infractions` | `infractions_count` | integer |
| `pv` | `violation_reports_count` | integer |
| `deroutements` | `diversions_count` | integer |
| `immobilisations` | `immobilizations_count` | integer |
| `saisies` | `seizures_count` | integer |
| `arrestations` | `arrests_count` | integer |
| `sanctions` | `penalties_count` | integer |
| `calcule_le` | `calculated_at` | timestamp(0) |

## M12 Communications et alertes

### `communication_channels` (spécification : `canal_communication`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(40) |
| `envoi_automatique_actif` | `is_auto_dispatch_enabled` | boolean |
| `consentement_requis` | `is_consent_required` | boolean |

### `email_connectors` (spécification : `connecteur_email`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `fournisseur` | `supplier` | varchar(80) |
| `domaine` | `domain` | varchar(190) |
| `parametres` | `parameters` | jsonb |
| `actif` | `is_active` | boolean |

### `message_templates` (spécification : `message_modele`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(60) |
| `canal` | `channel` | varchar(40) |
| `langue` | `language` | varchar(40) |
| `objet` | `subject` | varchar(255) |
| `corps` | `body` | text |
| `formulaire` | `form` | varchar(40) |
| `actif` | `is_active` | boolean |

### `dispatch_rules` (spécification : `regle_envoi`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `modele_id` | `message_template_id` | bigint |
| `declencheur` | `trigger_event` | varchar(60) |
| `conditions` | `conditions` | jsonb |
| `frequence_minutes` | `frequency_minutes` | integer |
| `plafond_par_jour` | `daily_cap` | integer |
| `condition_arret` | `stop_condition` | varchar(120) |
| `mode` | `mode` | varchar(40) |
| `actif` | `is_active` | boolean |
| `modifiable_par_groupe_id` | `editable_by_permission_group_id` | bigint |

### `sent_messages` (spécification : `message_envoye`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `modele_id` | `message_template_id` | bigint |
| `regle_id` | `dispatch_rule_id` | bigint |
| `canal_id` | `communication_channel_id` | bigint |
| `navire_id` | `vessel_id` | bigint |
| `operation_id` | `operation_id` | bigint |
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `destinataire` | `recipient` | varchar(190) |
| `objet` | `subject` | varchar(255) |
| `corps` | `body` | text |
| `statut` | `status` | varchar(40) |
| `envoye_le` | `sent_at` | timestamp(0) |
| `accuse_le` | `acknowledged_at` | timestamp(0) |
| `declenche_par` | `triggered_by` | varchar(40) |
| `utilisateur_id` | `user_id` | bigint |
| `reponse_recue` | `reply_received` | boolean |

### `ai_agent_actions` (spécification : `agent_ia_action`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `action_id` | `action_id` | bigint |
| `demande_par` | `requested_by` | bigint |
| `instruction` | `instruction` | text |
| `parametres` | `parameters` | jsonb |
| `statut` | `status` | varchar(40) |
| `executee_le` | `executed_at` | timestamp(0) |
| `resultat_objet_type` | `result_object_type` | varchar(80) |
| `resultat_objet_id` | `result_object_id` | bigint |

### `internal_notes` (spécification : `note_interne`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `type` | `type` | varchar(40) |
| `navire_id` | `vessel_id` | bigint |
| `urgence` | `urgency` | varchar(40) |
| `destinataire_utilisateur_id` | `recipient_user_id` | bigint |
| `destinataire_equipe_id` | `recipient_team_id` | bigint |
| `echeance` | `due_date` | timestamp(0) |
| `texte_libre` | `free_text` | text |
| `auteur_id` | `author_id` | bigint |
| `agent_ia_action_id` | `ai_agent_action_id` | bigint |
| `lue` | `is_read` | boolean |
| `lue_le` | `read_at` | timestamp(0) |
| `relayee_par` | `relayed_by` | varchar(40) |
| `mc_evenement_id` | `logbook_entry_id` | bigint |
| `cree_le` | `created_at` | timestamp(0) |

## M10 Pilotage, rapports, archivage

### `scheduled_reports` (spécification : `rapport_programme`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `code` | `code` | varchar(60) |
| `frequence` | `frequency` | varchar(40) |
| `format` | `format` | varchar(40) |
| `destinataire_id` | `recipient_id` | bigint |
| `actif` | `is_active` | boolean |

### `export_log` (spécification : `export_journal`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `id` | `id` | bigint |
| `utilisateur_id` | `user_id` | bigint |
| `objet` | `subject` | varchar(120) |
| `exporte_le` | `exported_at` | timestamp(0) |

### `operation_archives` (spécification : `archive_operation`)

| Colonne v1 | Colonne v2 | Type |
|---|---|---|
| `operation_id` | `operation_id` | bigint |
| `archivee_le` | `archived_at` | timestamp(0) |
